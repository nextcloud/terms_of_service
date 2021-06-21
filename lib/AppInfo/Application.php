<?php
/**
 * @copyright Copyright (c) 2017 Lukas Reschke <lukas@statuscode.ch>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\TermsOfService\AppInfo;

use Exception;
use OC\Files\Filesystem;
use OC\Files\Storage\Wrapper\Wrapper;
use OCA\Registration\Events\PassedFormEvent;
use OCA\Registration\Events\ShowFormEvent;
use OCA\Registration\Events\ValidateFormEvent;
use OCA\TermsOfService\Checker;
use OCA\TermsOfService\Filesystem\StorageWrapper;
use OCA\TermsOfService\Listener\RegistrationIntegration;
use OCA\TermsOfService\Listener\UserDeletedListener;
use OCA\TermsOfService\Notifications\Notifier;
use OCA\TermsOfService\Dav\CheckPlugin;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Files\Storage\IStorage;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IManager;
use OCP\SabrePluginEvent;
use OCP\User\Events\UserDeletedEvent;
use OCP\Util;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

include_once __DIR__ . '/../../vendor/autoload.php';

class Application extends App implements IBootstrap {


	public const APPNAME = 'terms_of_service';


	public function __construct() {
		parent::__construct('terms_of_service');
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(UserDeletedEvent::class, UserDeletedListener::class);
		$context->registerEventListener(ShowFormEvent::class, RegistrationIntegration::class);
		$context->registerEventListener(ValidateFormEvent::class, RegistrationIntegration::class);
		$context->registerEventListener(PassedFormEvent::class, RegistrationIntegration::class);
	}

	public function boot(IBootContext $context): void {
		Util::connectHook('OC_Filesystem', 'preSetup', $this, 'addStorageWrapper');

		// FIXME currently disabled until we made sure all clients (Talk and files on Android and iOS, as well as desktop) handle this gracefully
//		$eventDispatcher = $context->getServerContainer()->get(IEventDispatcher::class);
//		$eventDispatcher->addListener('OCA\DAV\Connector\Sabre::addPlugin', function (SabrePluginEvent $event) use ($context) {
//			$eventServer = $event->getServer();
//
//			if ($eventServer !== null) {
//				// We have to register the CheckPlugin here and not info.xml,
//				// because info.xml plugins are loaded, after the
//				// beforeMethod:* hook has already been emitted.
//				$plugin = $context->getAppContainer()->get(CheckPlugin::class);
//				$eventServer->addPlugin($plugin);
//			}
//		});

		$context->injectFn([$this, 'registerNotifier']);
		$context->injectFn([$this, 'createNotificationOnFirstLogin']);
		$context->injectFn([$this, 'registerFrontend']);
	}

	public function registerFrontend(IRequest $request, IConfig $config, IUserSession $userSession): void {
		if (!\OC::$CLI) {
			// Only display the app on index.php except for public shares
			Util::addStyle('terms_of_service', 'overlay');

			if ($userSession->getUser() instanceof IUser
				&& strpos($request->getPathInfo(), '/s/') !== 0
				&& strpos($request->getPathInfo(), '/login/') !== 0
				&& substr($request->getScriptName(), 0 - strlen('/index.php')) === '/index.php') {
				Util::addScript('terms_of_service', 'terms_of_service-user');
			} else if ($config->getAppValue(self::APPNAME, 'tos_on_public_shares', '0') === '1') {
				Util::addScript('terms_of_service', 'terms_of_service-public');
			}
		}
	}

	public function addStorageWrapper(): void {
		Filesystem::addStorageWrapper(
			'terms_of_service', [$this, 'addStorageWrapperCallback'], -10
		);
	}

	/**
	 * @param string $mountPoint
	 * @param IStorage|Wrapper $storage
	 *
	 * @return StorageWrapper|IStorage
	 * @throws Exception
	 */
	public function addStorageWrapperCallback(string $mountPoint, IStorage $storage): IStorage {
		if (!\OC::$CLI) {
			try {
				return new StorageWrapper(
					[
						'storage' => $storage,
						'mountPoint' => $mountPoint,
						'request' => \OC::$server->get(IRequest::class),
						'checker' => \OC::$server->get(Checker::class),
					]
				);
			} catch (ContainerExceptionInterface $e) {
				\OC::$server->get(LoggerInterface::class)->error(
					$e->getMessage(),
					['exception' => $e]
				);
			}
		}

		return $storage;
	}

	public function registerNotifier(IManager $notificationManager): void {
		$notificationManager->registerNotifierService(Notifier::class);
	}

	public function createNotificationOnFirstLogin(IManager $notificationManager, EventDispatcherInterface $dispatcher): void {
		$dispatcher->addListener(IUser::class . '::firstLogin', function(GenericEvent $event) use ($notificationManager) {
			$user = $event->getSubject();
			if (!$user instanceof IUser) {
				return;
			}

			$notification = $notificationManager->createNotification();
			$notification->setApp('terms_of_service')
				->setDateTime(new \DateTime())
				->setSubject('accept_terms')
				->setObject('terms', '1')
				->setUser($user->getUID());
			$notificationManager->notify($notification);
		});
	}
}

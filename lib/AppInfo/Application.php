<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\AppInfo;

use Exception;
use OC\Files\Filesystem;
use OCA\DAV\Events\SabrePluginAddEvent;
use OCA\Registration\Events\PassedFormEvent;
use OCA\Registration\Events\ShowFormEvent;
use OCA\Registration\Events\ValidateFormEvent;
use OCA\TermsOfService\Checker;
use OCA\TermsOfService\Dav\CheckPlugin;
use OCA\TermsOfService\Filesystem\StorageWrapper;
use OCA\TermsOfService\Listener\RegistrationIntegration;
use OCA\TermsOfService\Listener\UserDeletedListener;
use OCA\TermsOfService\Notifications\Notifier;
use OCA\TermsOfService\PublicCapabilities;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Services\IAppConfig;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Files\Storage\IStorage;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IManager;
use OCP\User\Events\UserDeletedEvent;
use OCP\User\Events\UserFirstTimeLoggedInEvent;
use OCP\Util;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;

include_once __DIR__ . '/../../vendor/autoload.php';

class Application extends App implements IBootstrap {


	public const APPNAME = 'terms_of_service';


	public function __construct() {
		parent::__construct(self::APPNAME);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(UserDeletedEvent::class, UserDeletedListener::class);
		$context->registerEventListener(ShowFormEvent::class, RegistrationIntegration::class);
		$context->registerEventListener(ValidateFormEvent::class, RegistrationIntegration::class);
		$context->registerEventListener(PassedFormEvent::class, RegistrationIntegration::class);
		$context->registerCapability(PublicCapabilities::class);
	}

	public function boot(IBootContext $context): void {
		Util::connectHook('OC_Filesystem', 'preSetup', $this, 'addStorageWrapper');

		$eventDispatcher = $context->getServerContainer()->get(IEventDispatcher::class);
		$eventDispatcher->addListener(SabrePluginAddEvent::class, function (SabrePluginAddEvent $event) use ($context): void {
			$eventServer = $event->getServer();

			// We have to register the CheckPlugin here and not info.xml,
			// because info.xml plugins are loaded, after the
			// beforeMethod:* hook has already been emitted.
			$plugin = $context->getAppContainer()->get(CheckPlugin::class);
			$eventServer->addPlugin($plugin);
		});

		$context->injectFn($this->registerNotifier(...));
		$context->injectFn($this->createNotificationOnFirstLogin(...));
		$context->injectFn($this->registerFrontend(...));
	}

	public function registerFrontend(IRequest $request, IAppConfig $appConfig, IUserSession $userSession): void {
		// Ignore CLI
		/** @psalm-suppress UndefinedClass */
		if (\OC::$CLI) {
			return;
		}

		// Skip login-related pages
		// TODO: Add a universal way to specify skipped pages instead of hardcoding
		$skipPatterns = [
			// Login
			'#^/login$#',
			// Login Flow Grant must have the terms of service
			// so that the user can accept them before using the app
			// TODO: add a checkbox on the login instead, like on the Registration app
			'#^/login/(?!flow/grant|v2/grant)#',
			// SAML
			'#^/apps/user_saml/saml$#',
			'#^/apps/user_saml/saml/#',
			// user_oidc
			'#^/apps/user_oidc/code$#',
			'#^/apps/user_oidc/sls$#',
			'#^/apps/user_oidc/id4me$#',
			'#^/apps/user_oidc/id4me/code$#',
			// registration
			'#^/apps/registration(?:$|/)#',
		];
		if (array_filter($skipPatterns, fn (string $pattern): int|false => preg_match($pattern, $request->getPathInfo()))) {
			return;
		}

		if ($userSession->getUser() instanceof IUser) {
			// Logged-in user
			Util::addScript('terms_of_service', 'terms_of_service-user');
			Util::addStyle('terms_of_service', 'terms_of_service-user');
		} elseif ($appConfig->getAppValueBool('tos_on_public_shares') === true) {
			// Guests on public pages
			Util::addScript('terms_of_service', 'terms_of_service-public');
			Util::addStyle('terms_of_service', 'terms_of_service-public');
		}

	}

	public function addStorageWrapper(): void {
		Filesystem::addStorageWrapper(
			'terms_of_service', $this->addStorageWrapperCallback(...), -10
		);
	}

	/**
	 * @return StorageWrapper|IStorage
	 * @throws Exception
	 */
	public function addStorageWrapperCallback(string $mountPoint, IStorage $storage): IStorage {
		/** @psalm-suppress UndefinedClass */
		if (!\OC::$CLI) {
			try {
				return new StorageWrapper(
					[
						'storage' => $storage,
						'mountPoint' => $mountPoint,
						'request' => \OCP\Server::get(IRequest::class),
						'checker' => \OCP\Server::get(Checker::class),
					]
				);
			} catch (ContainerExceptionInterface $e) {
				\OCP\Server::get(LoggerInterface::class)->error(
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

	public function createNotificationOnFirstLogin(IManager $notificationManager, IEventDispatcher $dispatcher): void {
		$dispatcher->addListener(UserFirstTimeLoggedInEvent::class, function (UserFirstTimeLoggedInEvent $event) use ($notificationManager): void {
			$user = $event->getUser();
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

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

namespace OCA\TermsOfService\Controller;

use OCA\TermsOfService\AppInfo\Application;
use OCA\TermsOfService\Db\Entities\Signatory;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;

class SigningController extends Controller {
	/** @var string */
	private $userId;
	/** @var SignatoryMapper */
	private $signatoryMapper;
	/** @var IManager */
	private $notificationsManager;
	/** @var IUserManager */
	private $userManager;
	/** @var IConfig */
	private $config;
	/** @var ISession */
	private $session;


	public function __construct(
		string $appName,
		$UserId,
		IRequest $request,
		SignatoryMapper $signatoryMapper,
		IManager $notificationsManager,
		IUserManager $userManager,
		IConfig $config,
		ISession $session
	) {
		parent::__construct($appName, $request);
		$this->userId = $UserId;
		$this->signatoryMapper = $signatoryMapper;
		$this->notificationsManager = $notificationsManager;
		$this->userManager = $userManager;
		$this->config = $config;
		$this->session = $session;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $termId
	 *
	 * @return JSONResponse
	 */
	public function signTerms(int $termId): JSONResponse {
		$signatory = new Signatory();
		$signatory->setUserId($this->userId);
		$signatory->setTermsId($termId);
		$signatory->setTimestamp(time());

		$this->signatoryMapper->insert($signatory);

		$notification = $this->notificationsManager->createNotification();
		$notification->setApp('terms_of_service')
			->setSubject('accept_terms')
			->setObject('terms', '1')
			->setUser($this->userId);

		// Mark all notifications as processed …
		$this->notificationsManager->markProcessed($notification);

		return new JSONResponse();
	}


	/**
	 * @PublicPage
	 *
	 * @param int $termId
	 * @UseSession
	 * @return JSONResponse
	 */
	public function signTermsPublic(int $termId): JSONResponse {
		$uuid = $this->config->getAppValue(Application::APPNAME, 'term_uuid', '');
		$this->session->set('term_uuid', $uuid);

		return new JSONResponse();
	}


	/**
	 * @return JSONResponse
	 */
	public function resetAllSignatories(): JSONResponse {
		$this->signatoryMapper->deleteAllSignatories();
		$this->config->setAppValue(Application::APPNAME, 'term_uuid', uniqid());

		$notification = $this->notificationsManager->createNotification();
		$notification->setApp('terms_of_service')
			->setSubject('accept_terms')
			->setObject('terms', '1');

		// Mark all notifications as processed …
		$this->notificationsManager->markProcessed($notification);

		$notification->setDateTime(new \DateTime());

		// … so we can create new ones for every one, also users which already accepted.
		$this->userManager->callForSeenUsers(function(IUser $user) use ($notification) {
			$notification->setUser($user->getUID());
			$this->notificationsManager->notify($notification);
		});

		return new JSONResponse();
	}
}

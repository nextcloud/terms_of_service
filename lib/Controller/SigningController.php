<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
use OCA\TermsOfService\Events\SignaturesResetEvent;
use OCP\EventDispatcher\IEventDispatcher;

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

	/** @var IEventDispatcher */
	private $eventDispatcher;


	public function __construct(
		string $appName,
		$UserId,
		IRequest $request,
		SignatoryMapper $signatoryMapper,
		IManager $notificationsManager,
		IUserManager $userManager,
		IConfig $config,
		ISession $session,
		IEventDispatcher $eventDispatcher
	) {
		parent::__construct($appName, $request);
		$this->userId = $UserId;
		$this->signatoryMapper = $signatoryMapper;
		$this->notificationsManager = $notificationsManager;
		$this->userManager = $userManager;
		$this->config = $config;
		$this->session = $session;
		$this->eventDispatcher = $eventDispatcher;
	}

	protected function resetAllSignaturesEvent(): SignaturesResetEvent {
		return new SignaturesResetEvent();
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

		$event = $this->resetAllSignaturesEvent();
		$this->eventDispatcher->dispatchTyped($event);

		return new JSONResponse();
	}
}

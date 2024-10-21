<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Controller;

use OCA\TermsOfService\AppInfo\Application;
use OCA\TermsOfService\Db\Entities\Signatory;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;
use OCA\TermsOfService\Events\SignaturesResetEvent;
use OCP\EventDispatcher\IEventDispatcher;

class SigningController extends OCSController {
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
	 * @return DataResponse
	 */
	public function signTerms(int $termId): DataResponse {
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

		return new DataResponse();
	}


	/**
	 * @PublicPage
	 *
	 * @param int $termId
	 * @UseSession
	 * @return DataResponse
	 */
	public function signTermsPublic(int $termId): DataResponse {
		$uuid = $this->config->getAppValue(Application::APPNAME, 'term_uuid', '');
		$this->session->set('term_uuid', $uuid);

		return new DataResponse();
	}


	/**
	 * @return DataResponse
	 */
	public function resetAllSignatories(): DataResponse {
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

		return new DataResponse();
	}
}

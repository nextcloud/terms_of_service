<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Controller;

use OCA\TermsOfService\BackgroundJobs\CreateNotifications;
use OCA\TermsOfService\Db\Entities\Signatory;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Events\SignaturesResetEvent;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Services\IAppConfig;
use OCP\BackgroundJob\IJobList;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;
use OCP\ISession;
use OCP\Notification\IManager;

/**
 * @psalm-api
 */
class SigningController extends OCSController {
	public function __construct(
		string $appName,
		private readonly ?string $userId,
		IRequest $request,
		private readonly SignatoryMapper $signatoryMapper,
		private readonly IManager $notificationsManager,
		private readonly IAppConfig $appConfig,
		private readonly ISession $session,
		private readonly IEventDispatcher $eventDispatcher,
		protected IJobList $jobList,
	) {
		parent::__construct($appName, $request);
	}

	protected function resetAllSignaturesEvent(): SignaturesResetEvent {
		return new SignaturesResetEvent();
	}

	/**
	 * As a logged-in user sign the terms
	 *
	 * @param int $termId The terms the user signed
	 * @return DataResponse<Http::STATUS_OK, list<empty>, array{}>
	 *
	 * 200: Signed successfully
	 */
	#[NoAdminRequired]
	public function signTerms(int $termId): DataResponse {
		assert($this->userId !== null);

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
	 * As a guest sign the terms
	 *
	 * @return DataResponse<Http::STATUS_OK|Http::STATUS_NOT_ACCEPTABLE, list<empty>, array{}>
	 *
	 * 200: Signed successfully
	 * 406: The user is already authenticated and therefore not allowed to sign the terms through this endpoint
	 */
	#[PublicPage]
	#[UseSession]
	public function signTermsPublic(): DataResponse {
		if ($this->userId !== null) {
			return new DataResponse([], Http::STATUS_NOT_ACCEPTABLE);
		}

		$uuid = $this->appConfig->getAppValueString('term_uuid');
		$this->session->set('term_uuid', $uuid);

		return new DataResponse();
	}



	/**
	 * Reset the signatories of all accounts
	 *
	 * @return DataResponse<Http::STATUS_OK, list<empty>, array{}>
	 *
	 * 200: Reset successfully
	 */
	public function resetAllSignatories(): DataResponse {
		$this->signatoryMapper->deleteAllSignatories();
		$this->appConfig->setAppValueString('term_uuid', uniqid());

		// Schedule a job to generate notifications
		$this->appConfig->deleteAppValue('sent_notifications');
		$this->jobList->add(CreateNotifications::class);

		$event = $this->resetAllSignaturesEvent();
		$this->eventDispatcher->dispatchTyped($event);

		return new DataResponse();
	}
}

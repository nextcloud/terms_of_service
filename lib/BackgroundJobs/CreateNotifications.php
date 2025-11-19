<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\BackgroundJobs;

use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\QueuedJob;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use Psr\Log\LoggerInterface;

class CreateNotifications extends QueuedJob {
	public const BATCH_SIZE = 1000;
	protected ?INotification $notification = null;
	protected int $currentBatch = 0;

	public function __construct(
		protected IUserManager $userManager,
		protected IManager $notificationsManager,
		protected TermsMapper $termsMapper,
		protected SignatoryMapper $signatoryMapper,
		protected IAppConfig $appConfig,
		protected LoggerInterface $logger,
		ITimeFactory $time,
	) {
		parent::__construct($time);
	}

	protected function run($argument): void {
		if ($this->appConfig->getAppValueBool('sent_notifications')) {
			$this->logger->debug('ToS Notifications have already been sent');
			return;
		}

		$terms = $this->termsMapper->getTerms();
		if (empty($terms)) {
			$this->logger->debug('No terms available to sign');
			return;
		}

		$this->appConfig->setAppValueBool('sent_notifications', true);

		$this->notification = $this->notificationsManager->createNotification();
		$this->notification->setApp('terms_of_service')
			->setSubject('accept_terms')
			->setObject('terms', '1');

		// Mark all notifications as processed …
		$this->notificationsManager->markProcessed($this->notification);

		// … before generating new ones.
		$this->notification->setDateTime(new \DateTime());

		$this->notificationsManager->defer();
		$this->currentBatch = 0;
		$this->userManager->callForSeenUsers($this->callForSeenUsers(...));
		$this->notificationsManager->flush();
	}

	public function callForSeenUsers(IUser $user): void {
		if ($this->signatoryMapper->hasSignedByUser($user)) {
			// User already signed in the meantime
			$this->logger->debug('User ' . $user->getUID() . ' already signed ToS');
			return;
		}
		assert($this->notification !== null);

		$this->notification->setUser($user->getUID());
		$this->notificationsManager->notify($this->notification);

		// Make sure we don't create a too huge batch for the push notifications
		$this->currentBatch++;
		if ($this->currentBatch === self::BATCH_SIZE) {
			$this->notificationsManager->flush();
			$this->notificationsManager->defer();
			$this->currentBatch = 0;
		}
	}
}

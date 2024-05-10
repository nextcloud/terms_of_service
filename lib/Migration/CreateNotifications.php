<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Migration;

use OCP\IUser;
use OCP\IUserManager;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\Notification\IManager;

class CreateNotifications implements IRepairStep {

	/** @var IUserManager */
	protected $userManager;
	/** @var IManager */
	protected $notificationsManager;

	/**
	 * @param IUserManager $userManager
	 * @param IManager $notificationsManager
	 */
	public function __construct(IUserManager $userManager, IManager $notificationsManager) {
		$this->userManager = $userManager;
		$this->notificationsManager = $notificationsManager;
	}

	/**
	 * @return string
	 * @since 9.1.0
	 */
	public function getName(): string {
		return 'Create notifications for users that already logged in';
	}

	/**
	 * @param IOutput $output
	 * @throws \InvalidArgumentException
	 */
	public function run(IOutput $output) {
		$notification = $this->notificationsManager->createNotification();
		$notification->setApp('terms_of_service')
			->setDateTime(new \DateTime())
			->setSubject('accept_terms')
			->setObject('terms', '1');

		$output->startProgress();
		$this->userManager->callForSeenUsers(function(IUser $user) use ($notification, $output) {
			$notification->setUser($user->getUID());
			$this->notificationsManager->notify($notification);
			$output->advance();
		});
		$output->finishProgress();
	}
}

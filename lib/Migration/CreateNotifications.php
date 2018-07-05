<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
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

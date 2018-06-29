<?php
/**
 * @copyright Copyright (c) 2018 Joas Schilling <coding@schilljs.com>
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

namespace OCA\TermsOfService\Notifications;

use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	/** @var IFactory */
	protected $l10nFactory;

	/** @var IURLGenerator */
	protected $url;

	public function __construct(IFactory $l10nFactory, IURLGenerator $url) {
		$this->l10nFactory = $l10nFactory;
		$this->url = $url;
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 * @return INotification
	 * @throws \InvalidArgumentException When the notification was not prepared by a notifier
	 * @since 9.0.0
	 */
	public function prepare(INotification $notification, $languageCode) {
		if ($notification->getApp() !== 'terms_of_service') {
			throw new \InvalidArgumentException('Wrong app');
		}

		$l = $this->l10nFactory->get('terms_of_service', $languageCode);

		$notification->setParsedSubject($l->t('Terms of service have been modified!'))
			->setParsedMessage($l->t('You have to accept the newest version of the terms of service in order to be able to use this service.'))
			->setIcon($this->url->getAbsoluteURL($this->url->imagePath('terms_of_service', 'app-dark.svg')))
			// We simply link to the base page of Nextcloud for now since that will show the popup.
			->setLink($this->url->getAbsoluteURL(''));

		return $notification;
	}
}

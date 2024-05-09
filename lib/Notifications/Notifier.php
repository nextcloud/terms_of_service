<?php
/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Notifications;

use OCA\TermsOfService\Checker;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	/** @var IFactory */
	protected $l10nFactory;
	/** @var IURLGenerator */
	protected $url;
	/** @var IManager */
	protected $notificationManager;
	/** @var Checker */
	protected $checker;

	public function __construct(IFactory $l10nFactory, IURLGenerator $url, IManager $notificationManager, Checker $checker) {
		$this->l10nFactory = $l10nFactory;
		$this->url = $url;
		$this->notificationManager = $notificationManager;
		$this->checker = $checker;
	}

	/**
	 * Identifier of the notifier, only use [a-z0-9_]
	 *
	 * @return string
	 * @since 17.0.0
	 */
	public function getID(): string {
		return 'terms_of_service';
	}

	/**
	 * Human readable name describing the notifier
	 *
	 * @return string
	 * @since 17.0.0
	 */
	public function getName(): string {
		return $this->l10nFactory->get('terms_of_service')->t('Terms of service');
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 * @return INotification
	 * @throws \InvalidArgumentException When the notification was not prepared by a notifier
	 * @since 9.0.0
	 */
	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== 'terms_of_service') {
			throw new \InvalidArgumentException('Wrong app');
		}

		// When we render push notifications, the active user is not the one we are looking for.
		// Also we don't have any country information, so we just render them and continue.
		// The user will not see the notification in the end, when it's not necessary.
		if (!$this->isRenderingPushNotifications() && $this->checker->currentUserHasSigned()) {
			$this->notificationManager->markProcessed($notification);
			throw new \InvalidArgumentException('Resolved');
		}

		$l = $this->l10nFactory->get('terms_of_service', $languageCode);

		$notification->setParsedSubject($l->t('Terms of service have been modified!'))
			->setParsedMessage($l->t('You have to accept the newest version of the terms of service in order to be able to use this service.'))
			->setIcon($this->url->getAbsoluteURL($this->url->imagePath('terms_of_service', 'app-dark.svg')))
			// We simply link to the base page of Nextcloud for now since that will show the popup.
			->setLink($this->url->getAbsoluteURL('/'));

		return $notification;
	}

	protected function isRenderingPushNotifications(): bool {
		$exception = new \Exception();
		$trace = $exception->getTrace();
		foreach ($trace as $step) {
			if (isset($step['class']) && $step['class'] === 'OCA\Notifications\Push' &&
				isset($step['function']) && $step['function'] === 'pushToDevice') {
				return true;
			}
		}
		return false;
	}
}

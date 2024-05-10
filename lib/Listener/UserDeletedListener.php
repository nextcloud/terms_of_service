<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Listener;

use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\UserDeletedEvent;

class UserDeletedListener implements IEventListener {

	/** @var SignatoryMapper */
	private $signatoryMapper;

	public function __construct(SignatoryMapper $signatoryMapper) {
		$this->signatoryMapper = $signatoryMapper;
	}

	public function handle(Event $event): void {
		if (!($event instanceof UserDeletedEvent)) {
			// Unrelated
			return;
		}

		$this->signatoryMapper->deleteSignatoriesByUser($event->getUser());
	}
}

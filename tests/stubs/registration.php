<?php
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Events {

	use OCP\EventDispatcher\Event;
	use OCP\IUser;

	abstract class AFormEvent implements \OCP\EventDispatcher\Event {
		public const STEP_EMAIL = 'email';
		public const STEP_VERIFICATION = 'verification';
		public const STEP_USER = 'user';
		public function getRegistrationIdentifier(): string {
		}

		public function getStep(): string {
		}
	}
	class PassedFormEvent extends AFormEvent {
		public function getUser(): ?IUser {
		}
	}
	class ShowFormEvent extends AFormEvent {}
	class ValidateFormEvent extends AFormEvent {
		public function addError(string $error): void {}
	}
}

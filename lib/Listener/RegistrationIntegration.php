<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Listener;

use OCA\Registration\Events\PassedFormEvent;
use OCA\Registration\Events\ShowFormEvent;
use OCA\Registration\Events\ValidateFormEvent;
use OCA\TermsOfService\CountryDetector;
use OCA\TermsOfService\Db\Entities\Signatory;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;
use OCP\IUser;
use OCP\Util;

/**
 * @template-implements IEventListener<ShowFormEvent|ValidateFormEvent|PassedFormEvent>
 */
class RegistrationIntegration implements IEventListener {

	public function __construct(
		private readonly SignatoryMapper $signatoryMapper,
		private readonly TermsMapper $termsMapper,
		private readonly CountryDetector $countryDetector,
		private readonly IRequest $request,
	) {
	}

	public function handle(Event $event): void {
		if ($event instanceof ShowFormEvent) {
			if ($event->getStep() === $event::STEP_EMAIL) {
				$this->showEmailForm($event);
			}
		}

		if ($event instanceof ValidateFormEvent) {
			if ($event->getStep() === $event::STEP_EMAIL) {
				$this->validateEmailForm($event);
			}
		}

		if ($event instanceof PassedFormEvent) {
			if ($event->getStep() === $event::STEP_EMAIL) {
				$this->passedEmailForm($event);
			}
			if ($event->getStep() === $event::STEP_USER) {
				$this->passedUserForm($event);
			}
		}
	}

	public function showEmailForm(ShowFormEvent $event): void {
		if (!$this->needsToAcceptTerms()) {
			return;
		}

		Util::addScript('terms_of_service', 'terms_of_service-registration', 'registration');
		Util::addStyle('terms_of_service', 'terms_of_service-registration');
	}

	public function validateEmailForm(ValidateFormEvent $event): void {
		if (!$this->needsToAcceptTerms()) {
			return;
		}

		if (!$this->request->getParam('terms_of_service_accepted')) {
			$event->addError('You need to accept the Terms of service.');
		}
	}

	public function passedEmailForm(PassedFormEvent $event): void {
		if (!$this->needsToAcceptTerms()) {
			return;
		}

		$signatory = new Signatory();
		$signatory->setUserId('reg/' . $event->getRegistrationIdentifier());
		$signatory->setTermsId((int)$this->request->getParam('terms_of_service_accepted'));
		$signatory->setTimestamp(time());

		$this->signatoryMapper->insert($signatory);
	}

	public function passedUserForm(PassedFormEvent $event): void {
		if (!$this->needsToAcceptTerms()) {
			return;
		}

		$user = $event->getUser();
		if (!$user instanceof IUser) {
			return;
		}

		$this->signatoryMapper->updateUserId('reg/' . $event->getRegistrationIdentifier(), $user->getUID());
	}

	protected function needsToAcceptTerms(): bool {
		$countryCode = $this->countryDetector->getCountry();
		$terms = $this->termsMapper->getTermsForCountryCode($countryCode);

		// No terms that would need accepting
		return !empty($terms);
	}
}

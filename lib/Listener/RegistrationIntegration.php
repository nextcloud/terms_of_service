<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021 Joas Schilling <coding@schilljs.com>
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

namespace OCA\TermsOfService\Listener;

use OCA\Registration\Events\BeforeTemplateRenderedEvent;
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

class RegistrationIntegration implements IEventListener {

	/** @var SignatoryMapper */
	private $signatoryMapper;
	/** @var TermsMapper */
	private $termsMapper;
	/** @var CountryDetector */
	private $countryDetector;
	/** @var IRequest */
	private $request;

	public function __construct(SignatoryMapper $signatoryMapper,
								TermsMapper $termsMapper,
								CountryDetector $countryDetector,
								IRequest $request) {
		$this->signatoryMapper = $signatoryMapper;
		$this->termsMapper = $termsMapper;
		$this->countryDetector = $countryDetector;
		$this->request = $request;
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

		Util::addStyle('terms_of_service', 'overlay');
		Util::addScript('terms_of_service', 'terms_of_service-registration');
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
		$signatory->setTermsId((int) $this->request->getParam('terms_of_service_accepted'));
		$signatory->setTimestamp(time());

		$this->signatoryMapper->insert($signatory);
	}

	public function passedUserForm(PassedFormEvent $event): void {
		if (!$this->needsToAcceptTerms()) {
			return;
		}

		if (!$event->getUser() instanceof IUser) {
			return;
		}

		$this->signatoryMapper->updateUserId('reg/' . $event->getRegistrationIdentifier(), $event->getUser()->getUID());
	}

	protected function needsToAcceptTerms(): bool {
		$countryCode = $this->countryDetector->getCountry();
		$terms = $this->termsMapper->getTermsForCountryCode($countryCode);

		// No terms that would need accepting
		return !empty($terms);
	}
}

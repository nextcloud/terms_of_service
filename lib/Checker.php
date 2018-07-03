<?php
/**
 * @copyright Copyright (c) 2017 Lukas Reschke <lukas@statuscode.ch>
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

namespace OCA\TermsOfService;

use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCP\IUser;
use OCP\IUserSession;

class Checker {
	/** @var IUserSession */
	private $userSession;
	/** @var SignatoryMapper */
	private $signatoryMapper;
	/** @var TermsMapper */
	private $termsMapper;
	/** @var CountryDetector */
	private $countryDetector;

	public function __construct(IUserSession $userSession,
								SignatoryMapper $signatoryMapper,
								TermsMapper $termsMapper,
								CountryDetector $countryDetector) {
		$this->userSession = $userSession;
		$this->signatoryMapper = $signatoryMapper;
		$this->termsMapper = $termsMapper;
		$this->countryDetector = $countryDetector;
	}

	/**
	 * Whether the currently logged-in user has signed the terms and conditions
	 * for the login action
	 *
	 * @return bool
	 */
	public function currentUserHasSigned(): bool {
		$user = $this->userSession->getUser();
		if(!($user instanceof IUser)) {
			return false;
		}

		$countryCode = $this->countryDetector->getCountry();
		$signatories = $this->signatoryMapper->getSignatoriesByUser($user);
		if (!empty($signatories)) {
			$terms = $this->termsMapper->getTermsForCountryCode($countryCode);
			if (empty($terms)) {
				// No terms for the country, check for global terms
				$terms = $this->termsMapper->getTermsForCountryCode($countryCode);
				if (empty($terms)) {
					// No terms that would need accepting
					return true;
				}
			}

			foreach($signatories as $signatory) {
				foreach($terms as $term) {
					if((int)$term->getId() === (int)$signatory->getTermsId()) {
						return true;
					}
				}
			}
		}

		return false;
	}
}

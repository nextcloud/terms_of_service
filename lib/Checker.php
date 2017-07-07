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

namespace OCA\TermsAndConditions;

use OCA\TermsAndConditions\Db\Mapper\SignatoryMapper;
use OCA\TermsAndConditions\Db\Mapper\TermsMapper;
use OCA\TermsAndConditions\Types\AccessTypes;
use OCP\IRequest;
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

	public function __construct(IRequest $request,
								IUserSession $userSession,
								SignatoryMapper $signatoryMapper,
								TermsMapper $termsMapper,
								CountryDetector $countryDetector) {
		$this->request = $request;
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
	public function currentUserHasSigned() {
		$user = $this->userSession->getUser();
		if(!($user instanceof IUser)) {
			return false;
		}

		$countryCode = $this->countryDetector->getCountry();
		$signatories = $this->signatoryMapper->getSignatoriesByUser($user, AccessTypes::LOGIN);
		if(count($signatories) > 0) {
			$terms = $this->termsMapper->getTermsForCountryCode($countryCode);
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

	public function getSignedStorageIds() {
		$user = $this->userSession->getUser();
		if(!($user instanceof IUser)) {
			return [];
		}

		$countryCode = $this->countryDetector->getCountry();
		$signatories = $this->signatoryMapper->getSignatoriesByUser($user, AccessTypes::INTERNAL_SHARE);

		$storageIds = [];
		$terms = $this->termsMapper->getTerms();
		foreach($signatories as $signatory) {
			foreach($terms as $term) {
				if($term->getCountryCode() === $countryCode) {
					$storageIds[] = (int)$signatory->getMetadata();
				}
			}
		}

		return $storageIds;
	}

	/**
	 * Whether the current user has signed for a specific file id
	 *
	 * @param int $storageId
	 * @return bool
	 */
	public function currentUserHasSignedForStorage($storageId) {
		return in_array($storageId, $this->getSignedStorageIds(), true);
	}

	public function getSignedPublicShareIds() {
		$countryCode = $this->countryDetector->getCountry();
		$signatories = $this->signatoryMapper->getSignatoriesByRemoteAddress($this->request->getRemoteAddress(), AccessTypes::PUBLIC_SHARE);

		$publicShareIds = [];
		$cookieValue = $this->request->getCookie('TermsAndConditionsShareIdCookie');
		if($cookieValue === null) {
			return [];
		}
		$claimedSignatures = json_decode($cookieValue, true);
		if(!is_array($claimedSignatures)) {
			return [];
		}

		$terms = $this->termsMapper->getTerms();
		foreach($signatories as $signatory) {
			foreach($terms as $term) {
				if($term->getCountryCode() === $countryCode) {
					if(in_array($signatory->getMetadata(), $claimedSignatures, true)) {
						$publicShareIds[] = $signatory->getMetadata();
					}
				}
			}
		}

		return $publicShareIds;
	}

	/**
	 * Whether the current user has signed for a public share identifier
	 *
	 * @param string $shareToken
	 * @return bool
	 */
	public function currentRequestHasSignedForPublicShare($shareToken) {
		return in_array($shareToken, $this->getSignedPublicShareIds(), true);
	}
}

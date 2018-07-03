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

namespace OCA\TermsOfService\Controller;

use OCA\TermsOfService\Checker;
use OCA\TermsOfService\CountryDetector;
use OCA\TermsOfService\Db\Entities\Terms;
use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCA\TermsOfService\Db\Mapper\LanguageMapper;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCA\TermsOfService\Exceptions\TermsNotFoundException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\L10N\IFactory;

class TermsController extends Controller {
	/** @var IFactory */
	private $factory;
	/** @var TermsMapper */
	private $termsMapper;
	/** @var SignatoryMapper */
	private $signatoryMapper;
	/** @var CountryMapper */
	private $countryMapper;
	/** @var LanguageMapper */
	private $languageMapper;
	/** @var CountryDetector */
	private $countryDetector;
	/** @var Checker */
	private $checker;

	public function __construct(string $appName,
								IRequest $request,
								IFactory $factory,
								TermsMapper $termsMapper,
								SignatoryMapper $signatoryMapper,
								CountryMapper $countryMapper,
								LanguageMapper $languageMapper,
								CountryDetector $countryDetector,
								Checker $checker) {
		parent::__construct($appName, $request);
		$this->factory = $factory;
		$this->termsMapper = $termsMapper;
		$this->signatoryMapper = $signatoryMapper;
		$this->countryMapper = $countryMapper;
		$this->languageMapper = $languageMapper;
		$this->countryDetector = $countryDetector;
		$this->checker = $checker;
	}

	/**
	 * @PublicPage
	 * @return JSONResponse
	 */
	public function index(): JSONResponse {
		$unsortedTerms = $this->termsMapper->getTerms();
		$terms = [];
		foreach($unsortedTerms as $term) {
			$terms[$term->getId()] = $term;
		}

		$response = [
			'terms' => $terms,
			'countryCodes' => $this->countryMapper->getCountries(),
			'languageCodes' => $this->languageMapper->getLanguages(),
			'currentSession' => [
				'languageCode' => strtolower(substr($this->factory->findLanguage(), 0, 2)),
				'countryCode' => $this->countryDetector->getCountry(),
			],
			'signatories' => [
				'hasSignedLogin' => $this->checker->currentUserHasSigned(),
				'signedStorages' => $this->checker->getSignedStorageIds(),
				'signedPublicLinks' => $this->checker->getSignedPublicShareIds(),
			],
		];
		return new JSONResponse($response);
	}

	/**
	 * @param int $id
	 * @return JSONResponse
	 */
	public function destroy(int $id): JSONResponse {
		$terms = new Terms();
		$terms->setId($id);

		$this->termsMapper->delete($terms);
		$this->signatoryMapper->deleteTerm($terms);

		return new JSONResponse();
	}

	/**
	 * @param string $countryCode
	 * @param string $languageCode
	 * @param string $body
	 * @return JSONResponse
	 */
	public function create(string $countryCode,
						   string $languageCode,
						   string $body): JSONResponse {
		$update = false;
		try {
			// Update terms
			$terms = $this->termsMapper->getTermsForCountryCodeAndLanguageCode($countryCode, $languageCode);
			$update = true;
		} catch (TermsNotFoundException $e) {
			// Create new terms
			$terms = new Terms();
		}

		if(!isset($this->countryMapper->getCountries()[$countryCode], $this->languageMapper->getLanguages()[$languageCode])) {
			return new JSONResponse([], Http::STATUS_EXPECTATION_FAILED);
		}

		$terms->setCountryCode($countryCode);
		$terms->setLanguageCode($languageCode);
		$terms->setBody($body);

		if($update === true) {
			$this->termsMapper->update($terms);
		} else {
			$this->termsMapper->insert($terms);
		}

		return new JSONResponse($terms);
	}
}

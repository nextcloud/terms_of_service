<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Controller;

use OCA\TermsOfService\AppInfo\Application;
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
use OCP\IConfig;
use OCP\IRequest;
use OCP\L10N\IFactory;
use OCA\TermsOfService\Events\TermsCreatedEvent;
use OCP\EventDispatcher\IEventDispatcher;

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
	/** @var IConfig */
	private $config;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	public function __construct(string $appName,
								IRequest $request,
								IFactory $factory,
								TermsMapper $termsMapper,
								SignatoryMapper $signatoryMapper,
								CountryMapper $countryMapper,
								LanguageMapper $languageMapper,
								CountryDetector $countryDetector,
								Checker $checker,
								IConfig $config,
								IEventDispatcher $eventDispatcher
	) {
		parent::__construct($appName, $request);
		$this->factory = $factory;
		$this->termsMapper = $termsMapper;
		$this->signatoryMapper = $signatoryMapper;
		$this->countryMapper = $countryMapper;
		$this->languageMapper = $languageMapper;
		$this->countryDetector = $countryDetector;
		$this->checker = $checker;
		$this->config = $config;
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @PublicPage
	 * @return JSONResponse
	 */
	public function index(): JSONResponse {
		$currentCountry = $this->countryDetector->getCountry();
		$countryTerms = $this->termsMapper->getTermsForCountryCode($currentCountry);

		if ($this->config->getAppValue(Application::APPNAME, 'term_uuid', '') === '')
		{
			$this->config->getAppValue(Application::APPNAME, 'term_uuid', uniqid());
		}

		$response = [
			'terms' => $countryTerms,
			'languages' => $this->languageMapper->getLanguages(),
			'hasSigned' => $this->checker->currentUserHasSigned(),
		];
		return new JSONResponse($response);
	}

	/**
	 * @return JSONResponse
	 */
	public function getAdminFormData(): JSONResponse {
		$response = [
			'terms' => $this->termsMapper->getTerms(),
			'countries' => $this->countryMapper->getCountries(),
			'languages' => $this->languageMapper->getLanguages(),
			'tos_on_public_shares' => $this->config->getAppValue(Application::APPNAME, 'tos_on_public_shares', '0'),
			'tos_for_users' => $this->config->getAppValue(Application::APPNAME, 'tos_for_users', '1'),
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
	protected function createTermsCreatedEvent(): TermsCreatedEvent {
		return new TermsCreatedEvent();
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

		if (!$this->countryMapper->isValidCountry($countryCode) || !$this->languageMapper->isValidLanguage($languageCode)) {
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

		$event = $this->createTermsCreatedEvent();
		$this->eventDispatcher->dispatchTyped($event);

		return new JSONResponse($terms);
	}
}

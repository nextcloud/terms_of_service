<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService;

use MaxMind\Db\Reader;
use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCP\IRequest;

class CountryDetector {
	/** @var IRequest */
	private $request;
	/** @var CountryMapper */
	private $countryMapper;

	public function __construct(IRequest $request,
								CountryMapper $countryMapper) {
		$this->request = $request;
		$this->countryMapper = $countryMapper;
	}

	/**
	 * Get the country for the current user
	 * @return string
	 */
	public function getCountry(): string {
		try {
			$reader = new Reader(__DIR__ . '/../vendor/GeoLite2-Country.mmdb');
			$record = $reader->get($this->request->getRemoteAddress());
		} catch (\Exception $e) {
			return CountryMapper::GLOBAL;
		}

		if ($record === null || !isset($record['country']['iso_code'])) {
			// No match found, e.g. for local address like 127.0.0.1
			return CountryMapper::GLOBAL;
		}

		if ($this->countryMapper->isValidCountry($record['country']['iso_code'])) {
			return $record['country']['iso_code'];
		}

		return CountryMapper::GLOBAL;
	}
}

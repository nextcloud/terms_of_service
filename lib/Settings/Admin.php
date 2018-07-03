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

namespace OCA\TermsOfService\Settings;

use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCA\TermsOfService\Db\Mapper\LanguageMapper;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Admin implements ISettings {
	/** @var CountryMapper */
	private $countryMapper;
	/** @var LanguageMapper */
	private $languageMapper;

	public function __construct(CountryMapper $countryMapper,
								LanguageMapper $languageMapper) {
		$this->countryMapper = $countryMapper;
		$this->languageMapper = $languageMapper;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getForm(): TemplateResponse {
		return new TemplateResponse(
			'terms_of_service',
			'settings',
			[
				'countries' => $this->countryMapper->getCountries(),
				'languages' => $this->languageMapper->getLanguages(),
			],
			''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSection(): string {
		return 'terms_of_service';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPriority(): int {
		return 100;
	}
}

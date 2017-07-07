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

namespace OCA\TermsAndConditions\Db\Mapper;

use OCP\IL10N;

class CountryMapper {
	/** @var IL10N */
	private $l10n;

	public function __construct(IL10N $l10n) {
		$this->l10n = $l10n;
	}

	/**
	 * Whether the specified country code exists
	 *
	 * @param string $countryCode
	 * @return bool
	 */
	public function isValidCountry($countryCode) {
		return isset($this->getCountries()[$countryCode]);
	}

	/**
	 * Gets the countries as well as the two-letter code
	 *
	 * @return array
	 */
	public function getCountries() {
		$countries = [
			'--' => $this->l10n->t('Global'),
			'AF' => $this->l10n->t('Afghanistan'),
			'AX' => $this->l10n->t('Åland Islands'),
			'AL' => $this->l10n->t('Albania'),
			'DZ' => $this->l10n->t('Algeria'),
			'AS' => $this->l10n->t('American Samoa'),
			'AD' => $this->l10n->t('Andorra'),
			'AO' => $this->l10n->t('Angola'),
			'AI' => $this->l10n->t('Anguilla'),
			'AQ' => $this->l10n->t('Antarctica'),
			'AG' => $this->l10n->t('Antigua and Barbuda'),
			'AR' => $this->l10n->t('Argentina'),
			'AM' => $this->l10n->t('Armenia'),
			'AW' => $this->l10n->t('Aruba'),
			'AU' => $this->l10n->t('Australia'),
			'AT' => $this->l10n->t('Austria'),
			'AZ' => $this->l10n->t('Azerbaijan'),
			'BS' => $this->l10n->t('Bahamas'),
			'BH' => $this->l10n->t('Bahrain'),
			'BD' => $this->l10n->t('Bangladesh'),
			'BB' => $this->l10n->t('Barbados'),
			'BY' => $this->l10n->t('Belarus'),
			'BE' => $this->l10n->t('Belgium'),
			'BZ' => $this->l10n->t('Belize'),
			'BJ' => $this->l10n->t('Benin'),
			'BM' => $this->l10n->t('Bermuda'),
			'BT' => $this->l10n->t('Bhutan'),
			'BO' => $this->l10n->t('Bolivia (Plurinational State of)'),
			'BQ' => $this->l10n->t('Bonaire, Sint Eustatius and Saba'),
			'BA' => $this->l10n->t('Bosnia and Herzegovina'),
			'BW' => $this->l10n->t('Botswana'),
			'BV' => $this->l10n->t('Bouvet Island'),
			'BR' => $this->l10n->t('Brazil'),
			'IO' => $this->l10n->t('British Indian Ocean Territory'),
			'BN' => $this->l10n->t('Brunei Darussalam'),
			'BG' => $this->l10n->t('Bulgaria'),
			'BF' => $this->l10n->t('Burkina Faso'),
			'BI' => $this->l10n->t('Burundi'),
			'CV' => $this->l10n->t('Cabo Verde'),
			'KH' => $this->l10n->t('Cambodia'),
			'CM' => $this->l10n->t('Cameroon'),
			'CA' => $this->l10n->t('Canada'),
			'KY' => $this->l10n->t('Cayman Islands'),
			'CF' => $this->l10n->t('Central African Republic'),
			'TD' => $this->l10n->t('Chad'),
			'CL' => $this->l10n->t('Chile'),
			'CN' => $this->l10n->t('China'),
			'CX' => $this->l10n->t('Christmas Island'),
			'CC' => $this->l10n->t('Cocos (Keeling) Islands'),
			'CO' => $this->l10n->t('Colombia'),
			'KM' => $this->l10n->t('Comoros'),
			'CG' => $this->l10n->t('Congo'),
			'CD' => $this->l10n->t('Congo (Democratic Republic of the)'),
			'CK' => $this->l10n->t('Cook Islands'),
			'CR' => $this->l10n->t('Costa Rica'),
			'CI' => $this->l10n->t('Côte d\'Ivoire'),
			'HR' => $this->l10n->t('Croatia'),
			'CU' => $this->l10n->t('Cuba'),
			'CW' => $this->l10n->t('Curaçao'),
			'CY' => $this->l10n->t('Cyprus'),
			'CZ' => $this->l10n->t('Czechia'),
			'DK' => $this->l10n->t('Denmark'),
			'DJ' => $this->l10n->t('Djibouti'),
			'DM' => $this->l10n->t('Dominica'),
			'DO' => $this->l10n->t('Dominican Republic'),
			'EC' => $this->l10n->t('Ecuador'),
			'EG' => $this->l10n->t('Egypt'),
			'SV' => $this->l10n->t('El Salvador'),
			'GQ' => $this->l10n->t('Equatorial Guinea'),
			'ER' => $this->l10n->t('Eritrea'),
			'EE' => $this->l10n->t('Estonia'),
			'ET' => $this->l10n->t('Ethiopia'),
			'FK' => $this->l10n->t('Falkland Islands (Malvinas)'),
			'FO' => $this->l10n->t('Faroe Islands'),
			'FJ' => $this->l10n->t('Fiji'),
			'FI' => $this->l10n->t('Finland'),
			'FR' => $this->l10n->t('France'),
			'GF' => $this->l10n->t('French Guiana'),
			'PF' => $this->l10n->t('French Polynesia'),
			'TF' => $this->l10n->t('French Southern Territories'),
			'GA' => $this->l10n->t('Gabon'),
			'GM' => $this->l10n->t('Gambia'),
			'GE' => $this->l10n->t('Georgia'),
			'DE' => $this->l10n->t('Germany'),
			'GH' => $this->l10n->t('Ghana'),
			'GI' => $this->l10n->t('Gibraltar'),
			'GR' => $this->l10n->t('Greece'),
			'GL' => $this->l10n->t('Greenland'),
			'GD' => $this->l10n->t('Grenada'),
			'GP' => $this->l10n->t('Guadeloupe'),
			'GU' => $this->l10n->t('Guam'),
			'GT' => $this->l10n->t('Guatemala'),
			'GG' => $this->l10n->t('Guernsey'),
			'GN' => $this->l10n->t('Guinea'),
			'GW' => $this->l10n->t('Guinea-Bissau'),
			'GY' => $this->l10n->t('Guyana'),
			'HT' => $this->l10n->t('Haiti'),
			'HM' => $this->l10n->t('Heard Island and McDonald Islands'),
			'VA' => $this->l10n->t('Holy See'),
			'HN' => $this->l10n->t('Honduras'),
			'HK' => $this->l10n->t('Hong Kong'),
			'HU' => $this->l10n->t('Hungary'),
			'IS' => $this->l10n->t('Iceland'),
			'IN' => $this->l10n->t('India'),
			'ID' => $this->l10n->t('Indonesia'),
			'IR' => $this->l10n->t('Iran (Islamic Republic of)'),
			'IQ' => $this->l10n->t('Iraq'),
			'IE' => $this->l10n->t('Ireland'),
			'IM' => $this->l10n->t('Isle of Man'),
			'IL' => $this->l10n->t('Israel'),
			'IT' => $this->l10n->t('Italy'),
			'JM' => $this->l10n->t('Jamaica'),
			'JP' => $this->l10n->t('Japan'),
			'JE' => $this->l10n->t('Jersey'),
			'JO' => $this->l10n->t('Jordan'),
			'KZ' => $this->l10n->t('Kazakhstan'),
			'KE' => $this->l10n->t('Kenya'),
			'KI' => $this->l10n->t('Kiribati'),
			'KP' => $this->l10n->t('Korea (Democratic People\'s Republic of)'),
			'KR' => $this->l10n->t('Korea (Republic of)'),
			'KW' => $this->l10n->t('Kuwait'),
			'KG' => $this->l10n->t('Kyrgyzstan'),
			'LA' => $this->l10n->t('Lao People\'s Democratic Republic'),
			'LV' => $this->l10n->t('Latvia'),
			'LB' => $this->l10n->t('Lebanon'),
			'LS' => $this->l10n->t('Lesotho'),
			'LR' => $this->l10n->t('Liberia'),
			'LY' => $this->l10n->t('Libya'),
			'LI' => $this->l10n->t('Liechtenstein'),
			'LT' => $this->l10n->t('Lithuania'),
			'LU' => $this->l10n->t('Luxembourg'),
			'MO' => $this->l10n->t('Macao'),
			'MK' => $this->l10n->t('Macedonia (the former Yugoslav Republic of)'),
			'MG' => $this->l10n->t('Madagascar'),
			'MW' => $this->l10n->t('Malawi'),
			'MY' => $this->l10n->t('Malaysia'),
			'MV' => $this->l10n->t('Maldives'),
			'ML' => $this->l10n->t('Mali'),
			'MT' => $this->l10n->t('Malta'),
			'MH' => $this->l10n->t('Marshall Islands'),
			'MQ' => $this->l10n->t('Martinique'),
			'MR' => $this->l10n->t('Mauritania'),
			'MU' => $this->l10n->t('Mauritius'),
			'YT' => $this->l10n->t('Mayotte'),
			'MX' => $this->l10n->t('Mexico'),
			'FM' => $this->l10n->t('Micronesia (Federated States of)'),
			'MD' => $this->l10n->t('Moldova (Republic of)'),
			'MC' => $this->l10n->t('Monaco'),
			'MN' => $this->l10n->t('Mongolia'),
			'ME' => $this->l10n->t('Montenegro'),
			'MS' => $this->l10n->t('Montserrat'),
			'MA' => $this->l10n->t('Morocco'),
			'MZ' => $this->l10n->t('Mozambique'),
			'MM' => $this->l10n->t('Myanmar'),
			'NA' => $this->l10n->t('Namibia'),
			'NR' => $this->l10n->t('Nauru'),
			'NP' => $this->l10n->t('Nepal'),
			'NL' => $this->l10n->t('Netherlands'),
			'NC' => $this->l10n->t('New Caledonia'),
			'NZ' => $this->l10n->t('New Zealand'),
			'NI' => $this->l10n->t('Nicaragua'),
			'NE' => $this->l10n->t('Niger'),
			'NG' => $this->l10n->t('Nigeria'),
			'NU' => $this->l10n->t('Niue'),
			'NF' => $this->l10n->t('Norfolk Island'),
			'MP' => $this->l10n->t('Northern Mariana Islands'),
			'NO' => $this->l10n->t('Norway'),
			'OM' => $this->l10n->t('Oman'),
			'PK' => $this->l10n->t('Pakistan'),
			'PW' => $this->l10n->t('Palau'),
			'PS' => $this->l10n->t('Palestine, State of'),
			'PA' => $this->l10n->t('Panama'),
			'PG' => $this->l10n->t('Papua New Guinea'),
			'PY' => $this->l10n->t('Paraguay'),
			'PE' => $this->l10n->t('Peru'),
			'PH' => $this->l10n->t('Philippines'),
			'PN' => $this->l10n->t('Pitcairn'),
			'PL' => $this->l10n->t('Poland'),
			'PT' => $this->l10n->t('Portugal'),
			'PR' => $this->l10n->t('Puerto Rico'),
			'QA' => $this->l10n->t('Qatar'),
			'RE' => $this->l10n->t('Réunion'),
			'RO' => $this->l10n->t('Romania'),
			'RU' => $this->l10n->t('Russian Federation'),
			'RW' => $this->l10n->t('Rwanda'),
			'BL' => $this->l10n->t('Saint Barthélemy'),
			'SH' => $this->l10n->t('Saint Helena, Ascension and Tristan da Cunha'),
			'KN' => $this->l10n->t('Saint Kitts and Nevis'),
			'LC' => $this->l10n->t('Saint Lucia'),
			'MF' => $this->l10n->t('Saint Martin (French part)'),
			'PM' => $this->l10n->t('Saint Pierre and Miquelon'),
			'VC' => $this->l10n->t('Saint Vincent and the Grenadines'),
			'WS' => $this->l10n->t('Samoa'),
			'SM' => $this->l10n->t('San Marino'),
			'ST' => $this->l10n->t('Sao Tome and Principe'),
			'SA' => $this->l10n->t('Saudi Arabia'),
			'SN' => $this->l10n->t('Senegal'),
			'RS' => $this->l10n->t('Serbia'),
			'SC' => $this->l10n->t('Seychelles'),
			'SL' => $this->l10n->t('Sierra Leone'),
			'SG' => $this->l10n->t('Singapore'),
			'SX' => $this->l10n->t('Sint Maarten (Dutch part)'),
			'SK' => $this->l10n->t('Slovakia'),
			'SI' => $this->l10n->t('Slovenia'),
			'SB' => $this->l10n->t('Solomon Islands'),
			'SO' => $this->l10n->t('Somalia'),
			'ZA' => $this->l10n->t('South Africa'),
			'GS' => $this->l10n->t('South Georgia and the South Sandwich Islands'),
			'SS' => $this->l10n->t('South Sudan'),
			'ES' => $this->l10n->t('Spain'),
			'LK' => $this->l10n->t('Sri Lanka'),
			'SD' => $this->l10n->t('Sudan'),
			'SR' => $this->l10n->t('Suriname'),
			'SJ' => $this->l10n->t('Svalbard and Jan Mayen'),
			'SZ' => $this->l10n->t('Swaziland'),
			'SE' => $this->l10n->t('Sweden'),
			'CH' => $this->l10n->t('Switzerland'),
			'SY' => $this->l10n->t('Syrian Arab Republic'),
			'TW' => $this->l10n->t('Taiwan, Province of China'),
			'TJ' => $this->l10n->t('Tajikistan'),
			'TZ' => $this->l10n->t('Tanzania, United Republic of'),
			'TH' => $this->l10n->t('Thailand'),
			'TL' => $this->l10n->t('Timor-Leste'),
			'TG' => $this->l10n->t('Togo'),
			'TK' => $this->l10n->t('Tokelau'),
			'TO' => $this->l10n->t('Tonga'),
			'TT' => $this->l10n->t('Trinidad and Tobago'),
			'TN' => $this->l10n->t('Tunisia'),
			'TR' => $this->l10n->t('Turkey'),
			'TM' => $this->l10n->t('Turkmenistan'),
			'TC' => $this->l10n->t('Turks and Caicos Islands'),
			'TV' => $this->l10n->t('Tuvalu'),
			'UG' => $this->l10n->t('Uganda'),
			'UA' => $this->l10n->t('Ukraine'),
			'AE' => $this->l10n->t('United Arab Emirates'),
			'GB' => $this->l10n->t('United Kingdom of Great Britain and Northern Ireland'),
			'US' => $this->l10n->t('United States of America'),
			'UM' => $this->l10n->t('United States Minor Outlying Islands'),
			'UY' => $this->l10n->t('Uruguay'),
			'UZ' => $this->l10n->t('Uzbekistan'),
			'VU' => $this->l10n->t('Vanuatu'),
			'VE' => $this->l10n->t('Venezuela (Bolivarian Republic of)'),
			'VN' => $this->l10n->t('Viet Nam'),
			'VG' => $this->l10n->t('Virgin Islands (British)'),
			'VI' => $this->l10n->t('Virgin Islands (U.S.)'),
			'WF' => $this->l10n->t('Wallis and Futuna'),
			'EH' => $this->l10n->t('Western Sahara'),
			'YE' => $this->l10n->t('Yemen'),
			'ZM' => $this->l10n->t('Zambia'),
			'ZW' => $this->l10n->t('Zimbabwe'),
		];

		return $countries;
	}
}

<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Db\Entities;

use OCA\TermsOfService\ResponseDefinitions;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getCountryCode()
 * @method void setCountryCode(string $country)
 * @method string getLanguageCode()
 * @method void setLanguageCode(string $languageCode)
 * @method string getBody()
 * @method void setBody(string $body)
 *
 * @psalm-import-type TermsOfServiceTerms from ResponseDefinitions
 */
class Terms extends Entity implements \JsonSerializable {
	/** @var string */
	public $countryCode;
	/** @var string */
	public $languageCode;
	/** @var string */
	public $body;

	public function jsonSerialize(): array {
		$parsedown = new \Parsedown();

		return [
			'id' => $this->getId(),
			'countryCode' => $this->getCountryCode(),
			'languageCode' => $this->getLanguageCode(),
			'body' => $this->getBody(),
			'renderedBody' => $parsedown->text($this->getBody())
		];
	}
}

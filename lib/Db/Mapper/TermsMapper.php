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

use OCA\TermsAndConditions\Db\Entities\Terms;
use OCA\TermsAndConditions\Exceptions\TermsNotFoundException;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;

class TermsMapper extends Mapper {
	const TABLENAME = 'termsandconditions_terms';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLENAME, Terms::class);
	}

	/**
	 * Returns all terms and conditions for the country code
	 *
	 * @param string $countryCode
	 * @return Terms[]
	 * @throws TermsNotFoundException
	 */
	public function getTermsForCountryCode($countryCode) {
		$qb = $this->db->getQueryBuilder();
		$qb
			->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('country_code', $qb->createParameter('countryCode')));
		return $this->findEntities($qb->getSQL(), ['countryCode' => $countryCode]);
	}

	/**
	 * Returns the terms and conditions for the specified country and language
	 * code
	 *
	 * @param string $countryCode
	 * @param string $languageCode
	 * @return Terms
	 * @throws TermsNotFoundException
	 */
	public function getTermsForCountryCodeAndLanguageCode($countryCode, $languageCode) {
		$qb = $this->db->getQueryBuilder();
		$qb
			->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('country_code', $qb->createNamedParameter($countryCode)))
			->andWhere($qb->expr()->eq('language_code', $qb->createNamedParameter($languageCode)));
		$result = $qb->execute();
		$row = $result->fetch();
		$result->closeCursor();
		if($row === false) {
			throw new TermsNotFoundException();
		}
		return Terms::fromRow($row);
	}

	/**
	 * Returns all terms of service
	 *
	 * @return Terms[]
	 */
	public function getTerms() {
		$qb = $this->db->getQueryBuilder();
		$qb
			->select('*')
			->from($this->tableName);

		return $this->findEntities($qb->getSQL());
	}
}

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

/**
 * @method Terms mapRowToEntity(array $row)
 */
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
	 */
	public function getTermsForCountryCode($countryCode) {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->tableName)
			->where($query->expr()->eq('country_code', $query->createNamedParameter($countryCode)));

		$entities = [];
		$result = $query->execute();
		while ($row = $result->fetch()){
			$entities[] = $this->mapRowToEntity($row);
		}
		$result->closeCursor();

		return $entities;
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
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->tableName)
			->where($query->expr()->eq('country_code', $query->createNamedParameter($countryCode)))
			->andWhere($query->expr()->eq('language_code', $query->createNamedParameter($languageCode)));
		$result = $query->execute();
		$row = $result->fetch();
		$result->closeCursor();

		if ($row === false) {
			throw new TermsNotFoundException();
		}
		return $this->mapRowToEntity($row);
	}

	/**
	 * Returns all terms of service
	 *
	 * @return Terms[]
	 */
	public function getTerms() {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->tableName);

		$entities = [];
		$result = $query->execute();
		while ($row = $result->fetch()){
			$entities[] = $this->mapRowToEntity($row);
		}
		$result->closeCursor();

		return $entities;
	}
}

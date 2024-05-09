<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Db\Mapper;

use OCA\TermsOfService\Db\Entities\Terms;
use OCA\TermsOfService\Exceptions\TermsNotFoundException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @method Terms mapRowToEntity(array $row)
 */
class TermsMapper extends QBMapper {
	const TABLENAME = 'termsofservice_terms';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLENAME, Terms::class);
	}

	/**
	 * Returns all terms and conditions for the country code
	 *
	 * @param string $countryCode
	 * @return Terms[]
	 */
	public function getTermsForCountryCode(string $countryCode): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from(self::TABLENAME)
			->where($query->expr()->in('country_code', $query->createNamedParameter([CountryMapper::GLOBAL, $countryCode], IQueryBuilder::PARAM_STR_ARRAY)));

		$entities = [
			CountryMapper::GLOBAL => [],
			$countryCode => [],
		];

		$result = $query->execute();
		while ($row = $result->fetch()){
			$entities[$row['country_code']][] = $this->mapRowToEntity($row);
		}
		$result->closeCursor();

		if (empty($entities[$countryCode])) {
			return $entities[CountryMapper::GLOBAL];
		}

		return $entities[$countryCode];
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
	public function getTermsForCountryCodeAndLanguageCode(string $countryCode, string $languageCode): Terms {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from(self::TABLENAME)
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
	 * Returns all terms and conditions
	 *
	 * @return Terms[]
	 */
	public function getTerms(): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from(self::TABLENAME);

		$entities = [];
		$result = $query->execute();
		while ($row = $result->fetch()){
			$entities[(int) $row['id']] = $this->mapRowToEntity($row);
		}
		$result->closeCursor();

		return $entities;
	}
}

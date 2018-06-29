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

use OCA\TermsAndConditions\Db\Entities\Signatory;
use OCA\TermsAndConditions\Db\Entities\Terms;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\IUser;

/**
 * @method Signatory mapRowToEntity(array $row)
 */
class SignatoryMapper extends QBMapper {
	const TABLENAME = 'termsandconditions_signatories';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLENAME, Signatory::class);
	}

	/**
	 * Returns all signatories
	 *
	 * @return Signatory[]
	 */
	public function getSignatories(): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from(self::TABLENAME);

		$entities = [];
		$result = $query->execute();
		while ($row = $result->fetch()){
			$entities[] = $this->mapRowToEntity($row);
		}
		$result->closeCursor();

		return $entities;
	}

	/**
	 * Get all signatories of a specific type for an user
	 *
	 * @param IUser $user
	 * @param int $accessType
	 * @return Signatory[]
	 */
	public function getSignatoriesByUser(IUser $user,
										 int $accessType): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from(self::TABLENAME)
			->where($query->expr()->eq('user_id', $query->createNamedParameter($user->getUID())))
			->andWhere($query->expr()->eq('access_type', $query->createNamedParameter($accessType)));

		$entities = [];
		$result = $query->execute();
		while ($row = $result->fetch()){
			$entities[] = $this->mapRowToEntity($row);
		}
		$result->closeCursor();

		return $entities;
	}

	/**
	 * Get all signatories of a specific type for an IP address
	 *
	 * @param string $remoteAddress
	 * @param int $accessType
	 * @return Signatory[]
	 */
	public function getSignatoriesByRemoteAddress(string $remoteAddress,
												  int $accessType): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from(self::TABLENAME)
			->where($query->expr()->eq('remote_ip', $query->createNamedParameter($remoteAddress)))
			->andWhere($query->expr()->eq('access_type', $query->createNamedParameter($accessType)));

		$entities = [];
		$result = $query->execute();
		while ($row = $result->fetch()){
			$entities[] = $this->mapRowToEntity($row);
		}
		$result->closeCursor();

		return $entities;
	}

	/**
	 * Delete all signatories for a given Terms
	 * @param Terms $terms
	 */
	public function deleteTerm(Terms $terms) {
		$query = $this->db->getQueryBuilder();
		$query->delete(self::TABLENAME)
			->where($query->expr()->eq('terms_id', $query->createNamedParameter($terms->getId())));
		$query->execute();
	}

	/**
	 * Delete all signatories
	 */
	public function deleteAllSignatories() {
		$query = $this->db->getQueryBuilder();
		$query->delete(self::TABLENAME);
		$query->execute();
	}
}

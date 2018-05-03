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
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;
use OCP\IUser;

/**
 * @method Signatory mapRowToEntity(array $row)
 */
class SignatoryMapper extends Mapper {
	const TABLENAME = 'termsandconditions_signatories';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLENAME, Signatory::class);
	}

	/**
	 * Returns all signatories
	 *
	 * @return Signatory[]
	 */
	public function getSignatories() {
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

	/**
	 * Get all signatories of a specific type for an user
	 *
	 * @param IUser $user
	 * @param int $accessType
	 * @return Signatory[]
	 */
	public function getSignatoriesByUser(IUser $user,
										 $accessType) {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->tableName)
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
	public function getSignatoriesByRemoteAddress($remoteAddress,
												  $accessType) {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->tableName)
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
}

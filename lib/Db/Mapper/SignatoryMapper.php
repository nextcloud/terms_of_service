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

namespace OCA\TermsOfService\Db\Mapper;

use OCA\TermsOfService\Db\Entities\Signatory;
use OCA\TermsOfService\Db\Entities\Terms;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\IUser;

/**
 * @method Signatory mapRowToEntity(array $row)
 */
class SignatoryMapper extends QBMapper {
	const TABLENAME = 'termsofservice_sigs';

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
	 * @return Signatory[]
	 */
	public function getSignatoriesByUser(IUser $user): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from(self::TABLENAME)
			->where($query->expr()->eq('user_id', $query->createNamedParameter($user->getUID())));

		$entities = [];
		$result = $query->execute();
		while ($row = $result->fetch()){
			$entities[] = $this->mapRowToEntity($row);
		}
		$result->closeCursor();

		return $entities;
	}

	/**
	 * Update the signer of an entry
	 *
	 * Used e.g. by the registration app integration when updating from registration id to the real user id
	 *
	 * @param string $oldId
	 * @param string $newId
	 */
	public function updateUserId(string $oldId, string $newId): void {
		$query = $this->db->getQueryBuilder();
		$query->update(self::TABLENAME)
			->set('user_id', $query->createNamedParameter($newId))
			->where($query->expr()->eq('user_id', $query->createNamedParameter($oldId)));

		$query->execute();
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

	/**
	 * Delete all signatories for a given user
	 * @param IUser $user
	 */
	public function deleteSignatoriesByUser(IUser $user) {
		$query = $this->db->getQueryBuilder();
		$query->delete(self::TABLENAME)
			->where($query->expr()->eq('user_id', $query->createNamedParameter($user->getUID())));
		$query->execute();
	}
}

<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Db\Mapper;

use OCA\TermsOfService\Db\Entities\Signatory;
use OCA\TermsOfService\Db\Entities\Terms;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\IUser;

/**
 * @method Signatory mapRowToEntity(array $row)
 * @template-extends QBMapper<Signatory>
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
	 * Check if a user has signed the terms
	 */
	public function hasSignedByUser(IUser $user): bool {
		$query = $this->db->getQueryBuilder();
		$query->select($query->expr()->literal(1))
			->from(self::TABLENAME)
			->where($query->expr()->eq('user_id', $query->createNamedParameter($user->getUID())))
			->setMaxResults(1);

		$entities = [];
		$result = $query->executeQuery();
		$hasSigned = (bool)$result->fetchOne();
		$result->closeCursor();

		return $hasSigned;
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

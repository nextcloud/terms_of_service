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
		$qb = $this->db->getQueryBuilder();
		$qb
			->select('*')
			->from($this->tableName);

		return $this->findEntities($qb->getSQL());
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
		$qb = $this->db->getQueryBuilder();
		$qb
			->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('user_id', $qb->createParameter('uid')))
			->andWhere($qb->expr()->eq('access_type', $qb->createParameter('accessType')));
		return $this->findEntities($qb->getSQL(), ['uid' => $user->getUID(), 'accessType' => $accessType]);
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
		$qb = $this->db->getQueryBuilder();
		$qb
			->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('remote_ip', $qb->createParameter('remoteIp')))
			->andWhere($qb->expr()->eq('access_type', $qb->createParameter('accessType')));
		return $this->findEntities($qb->getSQL(), ['remoteIp' => $remoteAddress, 'accessType' => $accessType]);
	}
}

<?php
/**
 * @copyright Copyright (c) 2018 Joas Schilling <coding@schilljs.com>
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
namespace OCA\TermsOfService\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version1000Date20181122140802 extends SimpleMigrationStep {

	/** @var IDBConnection */
	protected $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param IOutput $output
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('termsofservice_sigs')) {
			$table = $schema->createTable('termsofservice_sigs');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('terms_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
			]);
			$table->addColumn('timestamp', 'integer', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options) {
		if (!$this->connection->tableExists('termsofservice_signatories')) {
			return;
		}

		$insert = $this->connection->getQueryBuilder();
		$insert->insert('termsofservice_sigs')
			->values([
				'terms_id' => $insert->createParameter('terms_id'),
				'user_id' => $insert->createParameter('user_id'),
				'timestamp' => $insert->createParameter('timestamp'),
			]);

		$query = $this->connection->getQueryBuilder();
		$query->select('*')
			->from('termsofservice_signatories');

		$result = $query->execute();
		while ($row = $result->fetch()) {
			$insert
				->setParameter('terms_id', (int) $row['terms_id'], IQueryBuilder::PARAM_INT)
				->setParameter('user_id', $row['user_id'])
				->setParameter('timestamp', (int) $row['timestamp'], IQueryBuilder::PARAM_INT);
			$insert->execute();
		}
		$result->closeCursor();
	}
}

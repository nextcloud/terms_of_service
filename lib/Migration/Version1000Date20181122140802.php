<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\TermsOfService\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20181122140802 extends SimpleMigrationStep {

	/** @var IDBConnection */
	protected $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
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
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 */
	public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options): void {
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

		$result = $query->executeQuery();
		while ($row = $result->fetch()) {
			$insert
				->setParameter('terms_id', (int)$row['terms_id'], IQueryBuilder::PARAM_INT)
				->setParameter('user_id', $row['user_id'])
				->setParameter('timestamp', (int)$row['timestamp'], IQueryBuilder::PARAM_INT);
			$insert->executeStatement();
		}
		$result->closeCursor();
	}
}

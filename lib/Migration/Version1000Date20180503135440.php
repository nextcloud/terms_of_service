<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\TermsOfService\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20180503135440 extends SimpleMigrationStep {

	/**
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @return null|ISchemaWrapper
	 * @since 13.0.0
	 */
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('termsofservice_terms')) {
			$table = $schema->createTable('termsofservice_terms');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('country_code', 'string', [
				'notnull' => true,
				'length' => 2,
			]);
			$table->addColumn('language_code', 'string', [
				'notnull' => true,
				'length' => 2,
			]);
			$table->addColumn('body', 'text', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
		}

		/**
		 * Replaced by Version1000Date20181122140802
		 * if (!$schema->hasTable('termsofservice_signatories')) {
		 * $table = $schema->createTable('termsofservice_signatories');
		 * $table->addColumn('id', 'integer', [
		 * 'autoincrement' => true,
		 * 'notnull' => true,
		 * ]);
		 * $table->addColumn('terms_id', 'integer', [
		 * 'notnull' => true,
		 * ]);
		 * $table->addColumn('user_id', 'string', [
		 * 'notnull' => true,
		 * ]);
		 * $table->addColumn('timestamp', 'integer', [
		 * 'notnull' => true,
		 * ]);
		 * $table->setPrimaryKey(['id']);
		 * }
		 */
		return $schema;
	}

}

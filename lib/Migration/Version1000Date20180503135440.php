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
namespace OCA\TermsAndConditions\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version1000Date20180503135440 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 * @since 13.0.0
	 */
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('termsandconditions_terms')) {
			$table = $schema->createTable('termsandconditions_terms');
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

		if (!$schema->hasTable('termsandconditions_signatories')) {
			$table = $schema->createTable('termsandconditions_signatories');
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
			$table->addColumn('remote_ip', 'string', [
				'notnull' => true,
			]);
			$table->addColumn('access_type', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('metadata', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('timestamp', 'integer', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
		}
		return $schema;
	}

}

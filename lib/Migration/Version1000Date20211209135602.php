<?php
declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author John Molakvoæ <skjnldsv@protonmail.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\TermsOfService\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20211209135602 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->getTable('termsofservice_sigs');
		if (!$table->hasIndex('tos_sigs_terms_id')) {
			$table->addIndex(['terms_id'], 'tos_sigs_terms_id');
		}
		if (!$table->hasIndex('tos_sigs_user_id')) {
			$table->addIndex(['user_id'], 'tos_sigs_user_id');
		}

		$table = $schema->getTable('termsofservice_terms');
		if (!$table->hasIndex('tos_terms_country_code')) {
			$table->addIndex(['country_code'], 'tos_terms_country_code');
		}
		if (!$table->hasIndex('tos_terms_language_code')) {
			$table->addIndex(['language_code'], 'tos_terms_language_code');
		}

		return $schema;
	}
}

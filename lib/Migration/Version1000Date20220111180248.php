<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\TermsOfService\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20220111180248 extends SimpleMigrationStep {
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

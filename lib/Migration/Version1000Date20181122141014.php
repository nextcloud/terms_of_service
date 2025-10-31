<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\TermsOfService\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20181122141014 extends SimpleMigrationStep {

	/**
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('termsofservice_signatories')) {
			$schema->dropTable('termsofservice_signatories');
		}

		return $schema;
	}

}

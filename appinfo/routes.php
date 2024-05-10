<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
	'resources' => [
			'terms' => [
				'url' => '/terms'
			],
	],
	'routes' => [
		[
			'name' => 'Terms#getAdminFormData',
			'url' => '/terms/admin',
			'verb' => 'GET',
		],
		[
			'name' => 'Signing#signTerms',
			'url' => '/sign',
			'verb' => 'POST',
		],
		[
			'name' => 'Signing#signTermsPublic',
			'url' => '/sign_public',
			'verb' => 'POST',
		],
		[
			'name' => 'Signing#resetAllSignatories',
			'url' => '/sign',
			'verb' => 'DELETE',
		],
	],
];

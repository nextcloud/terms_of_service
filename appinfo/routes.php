<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
	'ocs' => [
		[
			'name' => 'Terms#index',
			'url' => '/terms',
			'verb' => 'GET',
		],
		[
			'name' => 'Terms#create',
			'url' => '/terms',
			'verb' => 'POST',
		],
		[
			'name' => 'Terms#destroy',
			'url' => '/terms/{id}',
			'verb' => 'DELETE',
		],
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

<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService;

use OCP\AppFramework\Services\IAppConfig;
use OCP\Capabilities\IPublicCapability;

class PublicCapabilities implements IPublicCapability {
	public function __construct(
		private readonly IAppConfig $config,
	) {
	}

	/**
	 * @return array{
	 *     terms_of_service: array{
	 *         enabled: true,
	 *         term_uuid: string,
	 *     },
	 * }
	 */
	public function getCapabilities(): array {
		$termId = $this->config->getAppValueString('term_uuid');

		return [
			'terms_of_service' => [
				'enabled' => true,
				'term_uuid' => $termId,
			],
		];
	}
}

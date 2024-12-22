<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService;

use OCA\TermsOfService\AppInfo\Application;
use OCP\Capabilities\IPublicCapability;
use OCP\IConfig;

class PublicCapabilities implements IPublicCapability {
	public function __construct(
		private IConfig $config,
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
		$termId = $this->config->getAppValue(Application::APPNAME, 'term_uuid');

		return [
			'terms_of_service' => [
				'enabled' => true,
				'term_uuid' => $termId,
			],
		];
	}
}

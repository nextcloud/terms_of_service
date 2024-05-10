<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Admin implements ISettings {
	/**
	 * {@inheritdoc}
	 */
	public function getForm(): TemplateResponse {
		return new TemplateResponse('terms_of_service', 'settings', [], TemplateResponse::RENDER_AS_BLANK
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSection(): string {
		return 'terms_of_service';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPriority(): int {
		return 100;
	}
}

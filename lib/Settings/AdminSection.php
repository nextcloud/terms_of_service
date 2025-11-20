<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;
use Override;

class AdminSection implements IIconSection {
	public function __construct(
		private readonly IL10N $l,
		private readonly IURLGenerator $url,
	) {
	}

	#[Override]
	public function getID(): string {
		return 'terms_of_service';
	}

	#[Override]
	public function getName(): string {
		return $this->l->t('Terms of service');
	}

	#[Override]
	public function getPriority(): int {
		return 60;
	}

	#[Override]
	public function getIcon(): string {
		return $this->url->imagePath('terms_of_service', 'app-dark.svg');
	}
}

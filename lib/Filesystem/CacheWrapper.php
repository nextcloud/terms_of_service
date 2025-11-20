<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\TermsOfService\Filesystem;

use OC\Files\Cache\Wrapper\CacheWrapper as Wrapper;
use OCP\Constants;
use OCP\Files\Cache\ICache;
use OCP\Files\Cache\ICacheEntry;
use Override;

class CacheWrapper extends Wrapper {
	public function __construct(
		ICache $cache,
		private readonly Helper $helper,
	) {
		parent::__construct($cache);
	}

	/**
	 * @param ICacheEntry|false $entry
	 */
	#[Override]
	protected function formatCacheEntry($entry): ICacheEntry|false {
		if ($entry !== false && isset($entry['path'], $entry['permissions'])
			&& !$this->helper->verifyAccess($entry['path'])) {
			$mask = Constants::PERMISSION_ALL
				& ~Constants::PERMISSION_READ
				& ~Constants::PERMISSION_CREATE
				& ~Constants::PERMISSION_UPDATE
				& ~Constants::PERMISSION_DELETE;
			$entry['permissions'] &= $mask;
		}
		return $entry;
	}
}

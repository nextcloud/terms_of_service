<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\TermsOfService\Filesystem;

use OC\Files\Cache\Wrapper\CacheWrapper as Wrapper;
use OCP\Constants;
use OCP\Files\Cache\ICache;
use OCP\Files\Storage\IStorage;

class CacheWrapper extends Wrapper {
	protected int $mask;

	public function __construct(
		ICache $cache,
		protected IStorage $storage,
		private Helper $helper,
	) {
		parent::__construct($cache);

		$this->mask = Constants::PERMISSION_ALL;
		$this->mask &= ~Constants::PERMISSION_READ;
		$this->mask &= ~Constants::PERMISSION_CREATE;
		$this->mask &= ~Constants::PERMISSION_UPDATE;
		$this->mask &= ~Constants::PERMISSION_DELETE;
	}

	public const PERMISSION_CREATE = 4;
	public const PERMISSION_READ = 1;
	public const PERMISSION_UPDATE = 2;
	public const PERMISSION_DELETE = 8;

	protected function formatCacheEntry($entry) {
		if (isset($entry['path'], $entry['permissions'])
			&& !$this->helper->verifyAccess($entry['path'])) {
			$entry['permissions'] &= $this->mask;
		}
		return $entry;
	}
}

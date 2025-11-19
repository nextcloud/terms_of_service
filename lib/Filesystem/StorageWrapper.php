<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Filesystem;

use OC\Files\Storage\Wrapper\Wrapper;
use OCP\Files\Cache\ICache;
use OCP\Files\ForbiddenException;

class StorageWrapper extends Wrapper {
	public string $mountPoint;
	private readonly Helper $helper;

	public function __construct($parameters) {
		parent::__construct($parameters);
		$this->mountPoint = $parameters['mountPoint'];

		$this->helper = new Helper($parameters['checker'], $this->mountPoint);
	}

	public function isCreatable($path): bool {
		if (!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isCreatable($path);
	}

	public function isUpdatable($path): bool {
		if (!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isUpdatable($path);
	}

	public function isDeletable($path): bool {
		if (!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isDeletable($path);
	}

	public function isReadable($path): bool {
		if (!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isReadable($path);
	}

	public function isSharable($path): bool {
		if (!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isReadable($path);
	}

	public function fopen($path, $mode) {
		if ($this->helper->verifyAccess($path)) {
			return $this->storage->fopen($path, $mode);
		}

		throw new ForbiddenException('Terms of service not signed!', true);
	}

	/**
	 * get a cache instance for the storage
	 *
	 * @param string $path
	 * @param \OC\Files\Storage\Storage (optional) the storage to pass to the cache
	 * @return \OC\Files\Cache\Cache
	 */
	public function getCache($path = '', $storage = null): ICache {
		if (!$storage) {
			$storage = $this;
		}
		$cache = $this->storage->getCache($path, $storage);
		return new CacheWrapper($cache, $this->helper);
	}
}

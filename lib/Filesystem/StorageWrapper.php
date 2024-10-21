<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Filesystem;

use OC\Files\Storage\Wrapper\Wrapper;
use OCP\Files\Cache\ICache;
use OCP\Files\ForbiddenException;
use OCP\Files\Storage\IStorage;

class StorageWrapper extends Wrapper {
	/** @var string */
	public $mountPoint;
	/** @var Helper */
	private $helper;

	public function __construct($parameters) {
		parent::__construct($parameters);
		$this->mountPoint = $parameters['mountPoint'];

		$this->helper = new Helper($parameters['checker'], $this->mountPoint);
	}

	public function isCreatable(string $path): bool {
		if(!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isCreatable($path);
	}

	public function isUpdatable(string $path): bool {
		if(!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isUpdatable($path);
	}

	public function isDeletable(string $path): bool {
		if(!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isDeletable($path);
	}

	public function isReadable(string $path): bool {
		if(!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isReadable($path);
	}

	public function isSharable(string $path): bool {
		if(!$this->helper->verifyAccess($path)) {
			return false;
		}

		return $this->storage->isReadable($path);
	}

	public function fopen(string $path, string $mode) {
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
	public function getCache(string $path = '', ?IStorage $storage = null): ICache {
		if (!$storage) {
			$storage = $this;
		}
		$cache = $this->storage->getCache($path, $storage);
		return new CacheWrapper($cache, $storage, $this->helper);
	}
}

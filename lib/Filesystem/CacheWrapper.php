<?php

namespace OCA\TermsOfService\Filesystem;

use OC\Files\Cache\Wrapper\CacheWrapper as Wrapper;
use OCP\Constants;
use OCP\Files\Cache\ICache;
use OCP\Files\ForbiddenException;
use OCP\Files\Storage\IStorage;

class CacheWrapper extends Wrapper  {
	/** @var Helper */
	private $helper;
	/** @var StorageWrapper*/
	protected $storage;
	/** @var int */
	protected $mask;

	/**
	 * @param ICache $cache
	 * @param IStorage $storage
	 * @param Helper $helper
	 */
	public function __construct(ICache $cache, IStorage $storage, Helper $helper) {
		parent::__construct($cache);
		$this->storage = $storage;
		$this->helper = $helper;

		$this->mask = Constants::PERMISSION_ALL;
		$this->mask &= ~Constants::PERMISSION_READ;
		$this->mask &= ~Constants::PERMISSION_CREATE;
		$this->mask &= ~Constants::PERMISSION_UPDATE;
		$this->mask &= ~Constants::PERMISSION_DELETE;
	}

	const PERMISSION_CREATE = 4;
	const PERMISSION_READ = 1;
	const PERMISSION_UPDATE = 2;
	const PERMISSION_DELETE = 8;

	protected function formatCacheEntry($entry) {
		if (isset($entry['path'], $entry['permissions']) &&
			!$this->helper->verifyAccess($entry['path'])) {
			$entry['permissions'] &= $this->mask;
		}
		return $entry;
	}
}

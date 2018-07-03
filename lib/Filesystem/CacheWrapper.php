<?php
/**
 * @copyright Copyright (c) 2017 Lukas Reschke <lukas@statuscode.ch>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\TermsOfService\Filesystem;

use OC\Files\Cache\Wrapper\CacheWrapper as Wrapper;
use OCP\Constants;
use OCP\Files\Cache\ICache;
use OCP\Files\Storage\IStorage;

class CacheWrapper extends Wrapper {
	const PERMISSION_CREATE = 4;
	const PERMISSION_READ = 1;
	const PERMISSION_UPDATE = 2;
	const PERMISSION_DELETE = 8;

	/** @var StorageWrapper */
	private $storage;
	/** @var int */
	private $mask;
	/** @var Helper */
	private $helper;
	/** @var string */
	private $mountPoint;

	public function __construct(ICache $cache,
								IStorage $storage,
								Helper $helper,
								$mountPoint) {
		parent::__construct($cache);
		$this->storage = $storage;
		$this->helper = $helper;
		$this->mountPoint = $mountPoint;
		$this->mask = Constants::PERMISSION_ALL;
		$this->mask &= ~Constants::PERMISSION_READ;
		$this->mask &= ~Constants::PERMISSION_CREATE;
		$this->mask &= ~Constants::PERMISSION_UPDATE;
		$this->mask &= ~Constants::PERMISSION_DELETE;
	}

	protected function formatCacheEntry($entry) {
		if(!$this->helper->isBlockable($entry['path'], $this->mountPoint)) {
			return $entry;
		}

		/*
		FIXME: Prevent directory listening if path not accessible
		if($this->helper->verifyAccess($entry['path'], $this->mountPoint)) {
			return $entry;
		}

		$entry['permissions'] &= $this->mask;
		*/
		return $entry;
	}
}

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

use OC\Files\Storage\Wrapper\Wrapper;
use OCP\Files\ForbiddenException;

class StorageWrapper extends Wrapper {
	/** @var string */
	public $mountPoint;
	/** @var Helper */
	private $helper;

	public function __construct($parameters) {
		parent::__construct($parameters);
		$this->mountPoint = $parameters['mountPoint'];

		$this->helper = new Helper(
			$parameters['checker'],
			\OC::$server->getUserSession()
		);
	}

	public function isCreatable($path) {
		if(!$this->helper->verifyAccess($path, $this->mountPoint)) {
			return false;
		}

		return $this->storage->isCreatable($path);
	}

	public function isUpdatable($path) {
		if(!$this->helper->verifyAccess($path, $this->mountPoint)) {
			return false;
		}

		return $this->storage->isUpdatable($path);
	}

	public function isDeletable($path) {
		if(!$this->helper->verifyAccess($path, $this->mountPoint)) {
			return false;
		}

		return $this->storage->isDeletable($path);
	}

	public function isReadable($path) {
		if(!$this->helper->verifyAccess($path, $this->mountPoint)) {
			return false;
		}

		return $this->storage->isReadable($path);
	}

	public function isSharable($path) {
		if(!$this->helper->verifyAccess($path, $this->mountPoint)) {
			return false;
		}

		return $this->storage->isReadable($path);
	}

	public function fopen($path, $mode) {
		if ($this->helper->verifyAccess($path, $this->mountPoint)) {
			return $this->storage->fopen($path, $mode);
		}

		throw new ForbiddenException('Terms of service not signed!', true);
	}
}

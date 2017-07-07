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

namespace OCA\TermsAndConditions\AppInfo;

use OC\Files\Filesystem;
use OCA\Files_Trashbin\Storage;
use OCA\TermsAndConditions\Checker;
use OCA\TermsAndConditions\CountryDetector;
use OCA\TermsAndConditions\Db\Mapper\SignatoryMapper;
use OCA\TermsAndConditions\Db\Mapper\TermsMapper;
use OCA\TermsAndConditions\Filesystem\StorageWrapper;
use OCP\AppFramework\App;
use OCP\Files\Storage\IStorage;
use OCP\IRequest;
use OCP\IUserSession;

class Application extends App {
	/** @var string */
	private $appName;
	/** @var IRequest|null */
	private $request;
	/** @var IUserSession|null */
	private $userSession;
	/** @var SignatoryMapper|null */
	private $signatoryMapper;
	/** @var TermsMapper|null */
	private $termsMapper;
	/** @var CountryDetector|null */
	private $countryDetector;

	public function __construct(IRequest $request = null,
								IUserSession $userSession = null,
								SignatoryMapper $signatoryMapper = null,
								TermsMapper $termsMapper = null,
								CountryDetector $countryDetector = null) {
		$this->appName = 'termsandconditions';
		$this->request = $request;
		$this->userSession = $userSession;
		$this->signatoryMapper = $signatoryMapper;
		$this->termsMapper = $termsMapper;
		$this->countryDetector = $countryDetector;
		parent::__construct($this->appName);
	}

	public function addStorageWrapper() {
		Filesystem::addStorageWrapper($this->appName, [$this, 'addStorageWrapperCallback'], -10);
	}

	/**
	 * @internal
	 * @param $mountPoint
	 * @param IStorage $storage
	 * @return StorageWrapper|IStorage
	 */
	public function addStorageWrapperCallback($mountPoint, IStorage $storage) {
		if (!\OC::$CLI  && !$storage->instanceOfStorage(Storage::class)) {
			return new StorageWrapper([
				'storage' => $storage,
				'mountPoint' => $mountPoint,
				'request' => $this->request,
				'checker' => new Checker(
					$this->request,
					$this->userSession,
					$this->signatoryMapper,
					$this->termsMapper,
					$this->countryDetector
				),
			]);
		}
		return $storage;
	}
}

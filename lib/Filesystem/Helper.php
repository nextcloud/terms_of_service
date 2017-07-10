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

namespace OCA\TermsAndConditions\Filesystem;

use OCA\Files_Trashbin\Storage;
use OCA\TermsAndConditions\Checker;
use OCP\Files\Storage\IStorage;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

class Helper {
	/** @var Checker */
	private $checker;
	/** @var IRequest */
	private $request;
	/** @var IUserSession */
	private $userSession;
	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(Checker $checker,
								IRequest $request,
								IUserSession $userSession,
								IURLGenerator $urlGenerator) {
		$this->checker = $checker;
		$this->request = $request;
		$this->userSession = $userSession;
		$this->urlGenerator = $urlGenerator;
	}

	protected function isBlockablePath(IStorage $storage, $path) {
		if (get_class($storage) !== Storage::class && property_exists($storage, 'mountPoint')) {
			/** @var StorageWrapper $storage */
			$fullPath = $storage->mountPoint . $path;
		} else {
			$fullPath = $path;
		}

		if (substr_count($fullPath, '/') < 3) {
			return false;
		}

		// '', admin, 'files', 'path/to/file.txt'
		$segment = explode('/', $fullPath, 4);
		return isset($segment[2]) && in_array($segment[2], [
				'files',
				'thumbnails',
				'files_versions',
			]);
	}

	/**
	 * Check if we are in the LoginController and if so, ignore the firewall
	 * @return bool
	 */
	protected function isCreatingSkeletonFiles() {
		$exception = new \Exception();
		$trace = $exception->getTrace();
		foreach ($trace as $step) {
			if (isset($step['class']) && $step['class'] === 'OC\Core\Controller\LoginController' &&
				isset($step['function']) && $step['function'] === 'tryLogin') {
				return true;
			}
		}
		return false;
	}


	public function isBlockable(IStorage $storage,
								$path) {
		if($this->isCreatingSkeletonFiles()) {
			return false;
		}

		return $this->isBlockablePath($storage, $path);
	}


	private function getPublicLinkShareToken() {
		$shareUrlStart =$this->urlGenerator->linkToRouteAbsolute('files_sharing.sharecontroller.showShare', ['token' => 'token']);
		$shareUrlStart = substr($shareUrlStart,0, -5);
		$currentUrl = $this->request->getServerProtocol() . '://' . $this->request->getServerHost() . $this->request->getRequestUri();

		if(substr($currentUrl, 0, strlen($shareUrlStart)) === $shareUrlStart) {
			return strstr(substr($currentUrl, strlen($shareUrlStart)), '/', true);
		}

		return null;
	}

	public function verifyAccess($path, $mountPoint) {
		// Check if it is a public link
		$publicShareToken = $this->getPublicLinkShareToken();
		if($publicShareToken !== null) {
			return $this->checker->currentRequestHasSignedForPublicShare($publicShareToken);
		}

		// Check if it is a shared storage and if the terms of conditions have been
		// signed already for it
		$path = preg_replace('/^files\//', '', $path);
		$node = \OC::$server->getUserFolder()->get($path);
		if($node->getOwner() !== $this->userSession->getUser()) {
			$mountpointPath = $mountPoint;
			$node = \OC::$server->getRootFolder()->get($mountpointPath);
			return $this->checker->currentUserHasSignedForStorage($node->getId());
		}

		// Check if the user has signed the terms and conditions already
		return $this->checker->currentUserHasSigned();
	}
}

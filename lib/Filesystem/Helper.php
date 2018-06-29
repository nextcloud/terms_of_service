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

use OC\Files\Storage\Wrapper\Wrapper;
use OCA\Files_Sharing\SharedStorage;
use OCA\TermsAndConditions\Checker;
use OCP\Files\Folder;
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

	protected function isBlockablePath(string $path, string $mountPoint): bool {
		$fullPath = $mountPoint . $path;

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
	protected function isCreatingSkeletonFiles(): bool {
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


	public function isBlockable(string $path,
								string $mountPoint): bool {
		if($this->isCreatingSkeletonFiles()) {
			return false;
		}

		return $this->isBlockablePath($path, $mountPoint);
	}

	private function getPublicLinkShareToken() {
		// Regular public sharing URLs
		$shareUrlStart =$this->urlGenerator->linkToRouteAbsolute('files_sharing.sharecontroller.showShare', ['token' => 'token']);
		$shareUrlStart = substr($shareUrlStart,0, -5);
		$currentUrl = $this->request->getServerProtocol() . '://' . $this->request->getServerHost() . $this->request->getRequestUri();

		if(substr($currentUrl, 0, strlen($shareUrlStart)) === $shareUrlStart) {
			return strstr(substr($currentUrl, strlen($shareUrlStart)), '/', true);
		}

		// /public.php/webdav
		$publicWebdavUrl = $this->urlGenerator->getAbsoluteURL('/public.php/webdav');

		if(substr($currentUrl, 0, strlen($publicWebdavUrl)) === $publicWebdavUrl) {
			return $_SERVER['PHP_AUTH_USER'];
		}
		return null;
	}

	public function verifyAccess(string $path, string $mountPoint, IStorage $storage): bool {
		// Check if it is a public link
		$publicShareToken = $this->getPublicLinkShareToken();
		if($publicShareToken !== null) {
			return $this->checker->currentRequestHasSignedForPublicShare($publicShareToken);
		}

		// Check if it is a shared storage and if the terms of conditions have been
		// signed already for it
		$userFolder = \OC::$server->getUserFolder();
		if($userFolder instanceof Folder) {
			$node = \OC::$server->getRootFolder()->get($mountPoint);

			if ($storage->instanceOfStorage(SharedStorage::class)) {
				if ($node->getOwner() !== $this->userSession->getUser()) {
					return $this->checker->currentUserHasSignedForStorage($node->getId());
				}
			}
		}

		// Check if the user has signed the terms and conditions already
		return $this->checker->currentUserHasSigned();
	}
}

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

use OCA\TermsOfService\Checker;
use OCP\IUserSession;

class Helper {
	/** @var Checker */
	private $checker;
	/** @var IUserSession */
	private $userSession;

	public function __construct(Checker $checker,
								IUserSession $userSession) {
		$this->checker = $checker;
		$this->userSession = $userSession;
	}

	protected function isBlockablePath(string $path, string $mountPoint): bool {
		$fullPath = $mountPoint . $path;

		if (substr_count($fullPath, '/') < 3) {
			return false;
		}

		// '', admin, 'files', 'path/to/file.txt'
		$segment = explode('/', $fullPath, 4);
		return isset($segment[2]) && \in_array($segment[2], [
				'files',
				'thumbnails',
				'files_versions',
			], true);
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


	protected function isBlockable(string $path,
								string $mountPoint): bool {
		if($this->isCreatingSkeletonFiles()) {
			return false;
		}

		return $this->isBlockablePath($path, $mountPoint);
	}

	public function verifyAccess(string $path, string $mountPoint): bool {
		if (!$this->userSession->isLoggedIn() || !$this->isBlockable($path, $mountPoint)) {
			return true;
		}

		// Check if the user has signed the terms and conditions already
		return $this->checker->currentUserHasSigned();
	}
}

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

use OC\Core\Controller\ClientFlowLoginController;
use OC\Core\Controller\ClientFlowLoginV2Controller;
use OC\Core\Controller\LoginController;
use OCA\Files_Sharing\Controller\ShareController;
use OCA\Registration\Controller\RegisterController;
use OCA\TermsOfService\Checker;

class Helper {
	/** @var Checker */
	private $checker;
	/** @var string */
	private $mountPoint;

	public function __construct(Checker $checker, string $mountPoint) {
		$this->checker = $checker;
		$this->mountPoint = $mountPoint;
	}

	protected function isBlockablePath(string $path): bool {
		$fullPath = $this->mountPoint . $path;

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
			if (isset($step['class'], $step['function'])
				&& $step['class'] === 'OC_Util'
				&& $step['function'] === 'copySkeleton') {
				return true;
			}

			if (isset($step['class'])
				&& (
					$step['class'] === LoginController::class
					|| $step['class'] === ClientFlowLoginController::class
					|| $step['class'] === ClientFlowLoginV2Controller::class
					|| $step['class'] === RegisterController::class
				)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if we are in the LoginController and if so, ignore the firewall
	 * @return bool
	 */
	protected function isValidatingShare(): bool {
		$exception = new \Exception();
		$trace = $exception->getTrace();
		foreach ($trace as $step) {
			if (isset($step['class'], $step['function']) &&
				$step['class'] === ShareController::class && $step['function'] === 'validateShare') {
				return true;
			}
		}
		return false;
	}


	protected function isBlockable(string $path): bool {
		if ($this->isCreatingSkeletonFiles() || $this->isValidatingShare()) {
			return false;
		}

		return $this->isBlockablePath($path);
	}

	public function verifyAccess(string $path): bool {
		if (!$this->isBlockable($path)) {
			return true;
		}

		// Check if the user has signed the terms and conditions already
		return $this->checker->currentUserHasSigned();
	}
}

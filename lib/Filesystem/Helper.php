<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Filesystem;

use OC\Core\Controller\ClientFlowLoginController;
use OC\Core\Controller\ClientFlowLoginV2Controller;
use OC\Core\Controller\LoginController;
use OCA\Files_Sharing\Controller\ShareController;
use OCA\Registration\Controller\RegisterController;
use OCA\TermsOfService\Checker;

class Helper {
	public function __construct(
		private readonly Checker $checker,
		private readonly string $mountPoint,
	) {
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
	 */
	protected function isValidatingShare(): bool {
		$exception = new \Exception();
		$trace = $exception->getTrace();
		foreach ($trace as $step) {
			if (isset($step['class'], $step['function'])
				&& $step['class'] === ShareController::class && $step['function'] === 'validateShare') {
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

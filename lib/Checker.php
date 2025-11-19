<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService;

use OCA\TermsOfService\AppInfo\Application;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCP\AppFramework\Services\IAppConfig;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

class Checker {
	private array $termsCache = [];

	public function __construct(
		private readonly IRequest $request,
		private readonly IUserSession $userSession,
		private readonly ISession $session,
		private readonly SignatoryMapper $signatoryMapper,
		private readonly TermsMapper $termsMapper,
		private readonly CountryDetector $countryDetector,
		private readonly IAppConfig $appConfig,
		private readonly \OCP\IAppConfig $globalAppConfig,
		private readonly LoggerInterface $logger,
		private readonly IURLGenerator $url,
	) {
	}

	public function getForbiddenMessage(): string {
		return $this->url->getBaseUrl();
	}

	/**
	 * Whether the currently logged-in user has signed the terms and conditions
	 * for the login action
	 */
	public function currentUserHasSigned(): bool {
		$uuid = $this->appConfig->getAppValueString('term_uuid');
		if ($this->userSession->getUser() === null) {
			if (!$this->appConfig->getAppValueBool('tos_on_public_shares')) {
				return true;
			}
		} else {
			if (!$this->appConfig->getAppValueBool('tos_for_users', true)) {
				return true;
			}
		}

		if ($this->isAllowedRequest()) {
			// Services such as Collabora doing requests for the user
			return true;
		}

		if ($this->session->get('term_uuid') === $uuid) {
			return true;
		}

		$countryCode = $this->countryDetector->getCountry();
		if (!array_key_exists($countryCode, $this->termsCache)) {
			$this->termsCache[$countryCode] = $this->termsMapper->getTermsForCountryCode($countryCode);
		}
		if (empty($this->termsCache[$countryCode])) {
			// No terms that would need accepting
			return true;
		}

		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return false;
		}

		$signatories = $this->signatoryMapper->getSignatoriesByUser($user);
		foreach ($signatories as $signatory) {
			foreach ($this->termsCache[$countryCode] as $term) {
				if ($term->getId() === $signatory->getTermsId()) {
					$this->session->set('term_uuid', $uuid);
					return true;
				}
			}
		}

		return false;
	}

	protected function isAllowedRequest(): bool {
		return $this->isRequestAllowedInConfig()
			|| $this->isValidWOPIRequest('richdocuments')
			|| $this->isValidWOPIRequest('officeonline');
	}

	protected function isRequestAllowedInConfig(): bool {
		$allowedPath = $this->appConfig->getAppValueString('allow_path_prefix');
		$allowedRanges = $this->allowedRangeForApp(Application::APPNAME, 'allow_ip_ranges');
		return $this->isRemoteAddressInRanges($allowedRanges)
			&& $this->isPathInfoStartingWith($allowedPath)
			&& $this->isAllowedScriptName();
	}

	protected function isValidWOPIRequest(string $app): bool {
		$allowedPath = '/apps/' . $app . '/wopi/';
		$allowedRanges = $this->allowedRangeForApp($app, 'wopi_allowlist');
		return $this->isRemoteAddressInRanges($allowedRanges)
			&& $this->isPathInfoStartingWith($allowedPath)
			&& $this->isAllowedScriptName();
	}

	protected function isPathInfoStartingWith(string $allowedPath): bool {
		// no path allowed
		if ($allowedPath === '') {
			return false;
		}
		$pathInfo = $this->request->getPathInfo();
		if ($pathInfo === false) {
			return false;
		}
		return str_starts_with($pathInfo, $allowedPath);
	}

	protected function isAllowedScriptName(): bool {
		return str_ends_with($this->request->getScriptName(), '/index.php');
	}

	protected function isRemoteAddressInRanges(array $allowedRanges): bool {
		$userIp = $this->request->getRemoteAddress();
		foreach ($allowedRanges as $range) {
			try {
				$match = $this->matchCidr($userIp, $range);
			} catch (\Error $e) {
				$this->logger->error('An error occurred while trying to validate a request against the WOPI allow list', ['exception' => $e]);
				continue;
			}

			if ($match) {
				return true;
			}
		}

		return false;
	}

	private function allowedRangeForApp(string $appId, string $configKey): array {
		$allowedRangesString = $this->globalAppConfig->getValueString($appId, $configKey);
		if ($allowedRangesString === '') {
			return [];
		}
		return explode(',', $allowedRangesString);
	}

	/**
	 * @copyright https://stackoverflow.com/questions/594112/matching-an-ip-to-a-cidr-mask-in-php-5/594134#594134
	 * @copyright (IPv4) https://stackoverflow.com/questions/594112/matching-an-ip-to-a-cidr-mask-in-php-5/594134#594134
	 * @copyright (IPv6) MW. https://stackoverflow.com/questions/7951061/matching-ipv6-address-to-a-cidr-subnet via
	 */
	private function matchCidr(string $ip, string $range): bool {
		/** @var string $subnet */
		[$subnet, $bits] = array_pad(explode('/', $range), 2, null);
		if ($bits === null) {
			$bits = 32;
		}
		$bits = (int)$bits;

		if ($this->isIpv4($ip) && $this->isIpv4($subnet)) {
			$mask = -1 << (32 - $bits);

			$ip = ip2long($ip);
			$subnet = ip2long($subnet);
			$subnet &= $mask;
			return ($ip & $mask) === $subnet;
		}

		if ($this->isIpv6($ip) && $this->isIPv6($subnet)) {
			$subnet = inet_pton($subnet);
			$ip = inet_pton($ip);

			$binMask = str_repeat('f', (int)($bits / 4));
			switch ($bits % 4) {
				case 0:
					break;
				case 1:
					$binMask .= '8';
					break;
				case 2:
					$binMask .= 'c';
					break;
				case 3:
					$binMask .= 'e';
					break;
			}

			$binMask = str_pad($binMask, 32, '0');
			$binMask = pack('H*', $binMask);

			if (($ip & $binMask) === $subnet) {
				return true;
			}
		}
		return false;
	}

	private function isIpv4(string $ip): bool {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
	}

	private function isIpv6(string $ip): bool {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
	}
}

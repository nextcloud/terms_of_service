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

namespace OCA\TermsOfService;

use OCA\TermsOfService\AppInfo\Application;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCP\IConfig;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUserManager;
use OCP\IL10N;

class Checker {
	/** @var string */
	private $userId;
	/** @var IRequest */
	private $request;
	/** @var IUserManager */
	private $userManager;
	/** @var ISession */
	private $session;
	/** @var SignatoryMapper */
	private $signatoryMapper;
	/** @var TermsMapper */
	private $termsMapper;
	/** @var CountryDetector */
	private $countryDetector;
	/** @var IConfig */
	private $config;

	public function __construct(
		?string $userId,
		IRequest $request,
		IUserManager $userManager,
		ISession $session,
		SignatoryMapper $signatoryMapper,
		TermsMapper $termsMapper,
		CountryDetector $countryDetector,
		IConfig $config,
		IL10N $l10n
	) {
		$this->userId = $userId;
		$this->request = $request;
		$this->userManager = $userManager;
		$this->session = $session;
		$this->signatoryMapper = $signatoryMapper;
		$this->termsMapper = $termsMapper;
		$this->countryDetector = $countryDetector;
		$this->config = $config;
		$this->l10n = $l10n;
	}

	public function getForbiddenMessage(): string {
		return $this->l10n->t('Terms of service are not signed');
	}

	/**
	 * Whether the currently logged-in user has signed the terms and conditions
	 * for the login action
	 *
	 * @return bool
	 */
	public function currentUserHasSigned(): bool {
		$uuid = $this->config->getAppValue(Application::APPNAME, 'term_uuid', '');
		if ($this->userId === null) {
			if ($this->config->getAppValue(Application::APPNAME, 'tos_on_public_shares', '0') === '0') {
				return true;
			}
		} else {
			if ($this->config->getAppValue(Application::APPNAME, 'tos_for_users', '1') !== '1') {
				return true;
			}
		}

		if ($this->isValidWOPIRequest()) {
			// Richdocuments and Collabora doing WOPI requests for the user
			return true;
		}

		if ($this->session->get('term_uuid') === $uuid) {
			return true;
		}

		$countryCode = $this->countryDetector->getCountry();
		$terms = $this->termsMapper->getTermsForCountryCode($countryCode);
		if (empty($terms)) {
			// No terms that would need accepting
			return true;
		}

		if ($this->userId === null) {
			return false;
		}

		$user = $this->userManager->get($this->userId);

		$signatories = $this->signatoryMapper->getSignatoriesByUser($user);
		if (!empty($signatories)) {
			foreach($signatories as $signatory) {
				foreach($terms as $term) {
					if((int)$term->getId() === (int)$signatory->getTermsId()) {
						return true;
					}
				}
			}
		}

		return false;
	}

	protected function isValidWOPIRequest(): bool {
		if (!$this->isWOPIRemoteAddress()) {
			return false;
		}

		return strpos($this->request->getPathInfo(), '/apps/richdocuments/wopi/') === 0
			&& substr($this->request->getScriptName(), 0 - strlen('/index.php')) === '/index.php';
	}

	protected function isWOPIRemoteAddress(): bool {
		$allowedRanges = $this->config->getAppValue('richdocuments', 'wopi_allowlist');
		if ($allowedRanges === '') {
			return true;
		}
		$allowedRanges = explode(',', $allowedRanges);

		$userIp = $this->request->getRemoteAddress();
		foreach ($allowedRanges as $range) {
			if ($this->matchCidr($userIp, $range)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @copyright https://stackoverflow.com/questions/594112/matching-an-ip-to-a-cidr-mask-in-php-5/594134#594134
	 * @copyright (IPv4) https://stackoverflow.com/questions/594112/matching-an-ip-to-a-cidr-mask-in-php-5/594134#594134
	 * @copyright (IPv6) MW. https://stackoverflow.com/questions/7951061/matching-ipv6-address-to-a-cidr-subnet via
	 */
	private function matchCidr(string $ip, string $range): bool {
		list($subnet, $bits) = array_pad(explode('/', $range), 2, null);
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

			$binMask = str_repeat("f", $bits / 4);
			switch ($bits % 4) {
				case 0:
					break;
				case 1:
					$binMask .= "8";
					break;
				case 2:
					$binMask .= "c";
					break;
				case 3:
					$binMask .= "e";
					break;
			}

			$binMask = str_pad($binMask, 32, '0');
			$binMask = pack("H*", $binMask);

			if (($ip & $binMask) === $subnet) {
				return true;
			}
		}
		return false;
	}

	private function isIpv4($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	private function isIpv6($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}
}

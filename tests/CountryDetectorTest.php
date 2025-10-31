<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Tests;

use OCA\TermsOfService\CountryDetector;
use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group DB
 */
class CountryDetectorTest extends \Test\TestCase {
	protected IRequest&MockObject $request;
	protected CountryMapper&MockObject $countryMapper;
	protected CountryDetector $detector;

	protected function setUp(): void {
		$this->request = $this->createMock(IRequest::class);
		$this->countryMapper = $this->createMock(CountryMapper::class);

		$this->detector = new CountryDetector(
			$this->request,
			$this->countryMapper
		);
	}

	public function dataGetCountry(): array {
		return [
			'Local'
				=> ['127.0.0.1', null, null, '--'],
			'No country only continent'
				=> ['138.199.26.4', null, null, '--'],
			'India'
				=> ['103.232.172.42', 'IN', true, 'IN'],
			'Germany'
				=> ['109.250.68.153', 'DE', true, 'DE'],
			'France'
				=> ['88.187.212.139', 'FR', true, 'FR'],
		];
	}

	/**
	 * @dataProvider dataGetCountry
	 * @param string $iso
	 * @param bool $valid
	 */
	public function testGetCountry(string $ip, ?string $iso, ?bool $valid, string $expected): void {
		$this->request->method('getRemoteAddress')
			->willReturn($ip);

		if ($valid === null) {
			$this->countryMapper->expects($this->never())
				->method('isValidCountry');
		} else {
			$this->countryMapper->expects($this->once())
				->method('isValidCountry')
				->with($iso)
				->willReturn($valid);
		}

		$this->assertSame($expected, $this->detector->getCountry());
	}
}

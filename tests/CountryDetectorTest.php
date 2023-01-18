<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2023 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OCA\TermsOfService\Tests;

use OCA\TermsOfService\CountryDetector;
use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;

class CountryDetectorTest extends \Test\TestCase {
	/** @var IRequest|MockObject */
	protected $request;
	/** @var CountryMapper|MockObject */
	protected $countryMapper;
	/** @var CountryDetector */
	protected $detector;

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
			'Local' =>
				['127.0.0.1', null, null, '--'],
			'No country only continent' =>
				['138.199.26.4', null, null, '--'],
			'India' =>
				['103.232.172.42', 'IN', true, 'IN'],
			'Germany' =>
				['109.250.68.153', 'DE', true, 'DE'],
			'France' =>
				['88.187.212.139', 'FR', true, 'FR'],
		];
	}

	/**
	 * @dataProvider dataGetCountry
	 * @param string $ip
	 * @param string $iso
	 * @param bool $valid
	 * @param string $expected
	 * @return void
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

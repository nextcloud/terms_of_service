<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\TermsOfService\Tests;

use OCA\TermsOfService\Command\DeleteTermsCommand;
use OCA\TermsOfService\Db\Entities\Terms;
use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCA\TermsOfService\Db\Mapper\LanguageMapper;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCA\TermsOfService\Exceptions\TermsNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @group Command
 */
final class DeleteTermsCommandTest extends TestCase {

	protected CountryMapper&MockObject $countryMapper;
	protected TermsMapper&MockObject $termsMapper;
	protected LanguageMapper&MockObject $languageMapper;
	protected SignatoryMapper&MockObject $signatoryMapper;
	protected Input&MockObject $inputInterface;
	protected ConsoleOutput&MockObject $outputInterface;
	protected DeleteTermsCommand $command;

	protected function setUp(): void {
		$this->countryMapper = $this->createMock(CountryMapper::class);
		$this->termsMapper = $this->createMock(TermsMapper::class);
		$this->languageMapper = $this->createMock(LanguageMapper::class);
		$this->signatoryMapper = $this->createMock(SignatoryMapper::class);
		$this->inputInterface = $this->createMock(Input::class);
		$this->outputInterface = $this->createMock(ConsoleOutput::class);

		$this->command = new DeleteTermsCommand(
			$this->termsMapper,
			$this->countryMapper,
			$this->languageMapper,
			$this->signatoryMapper,
		);
	}

	public function testDeletesExistingTerms(): void {
		$this->inputInterface->method('getOption')->willReturnMap(
			[
				['country', 'us'],
				['language', 'en'],
			]
		);

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with('us')
			->willReturn(true);

		$this->languageMapper->expects($this->once())
			->method('isValidLanguage')
			->with('en')
			->willReturn(true);

		$terms = new Terms();

		$this->termsMapper->expects($this->once())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with('us', 'en')
			->willReturn($terms);

		$this->termsMapper->expects($this->once())
			->method('delete')
			->with($terms);

		$this->signatoryMapper->expects($this->once())
			->method('deleteTerm')
			->with($terms);

		$result = $this->command->run($this->inputInterface, $this->outputInterface);
		$this->assertSame(Command::SUCCESS, $result);
	}

	public function testFallsBackToGlobalCountry(): void {
		$this->inputInterface->method('getOption')->willReturnMap(
			[
				['country', null],
				['language', 'en'],
			]
		);

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with(CountryMapper::GLOBAL)
			->willReturn(true);

		$this->languageMapper->expects($this->once())
			->method('isValidLanguage')
			->with('en')
			->willReturn(true);

		$terms = new Terms();

		$this->termsMapper->expects($this->once())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with(CountryMapper::GLOBAL, 'en')
			->willReturn($terms);

		$this->termsMapper->expects($this->once())
			->method('delete')
			->with($terms);

		$this->signatoryMapper->expects($this->once())
			->method('deleteTerm')
			->with($terms);

		$result = $this->command->run($this->inputInterface, $this->outputInterface);
		$this->assertSame(Command::SUCCESS, $result);
	}

	public function testRejectsInvalidCountry(): void {
		$this->inputInterface->method('getOption')->willReturnMap(
			[
				['country', 'invalid'],
				['language', 'en'],
			]
		);

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with('invalid')
			->willReturn(false);

		$this->languageMapper->expects($this->never())
			->method('isValidLanguage')
			->with('en')
			->willReturn(true);

		$this->termsMapper->expects($this->never())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with('invalid', 'en');

		$this->termsMapper->expects($this->never())
			->method('delete');

		$this->signatoryMapper->expects($this->never())
			->method('deleteTerm');

		$result = $this->command->run($this->inputInterface, $this->outputInterface);
		$this->assertSame(Command::FAILURE, $result);
	}

	public function testRejectsInvalidLanguage(): void {
		$this->inputInterface->method('getOption')->willReturnMap(
			[
				['country', 'us'],
				['language', 'invalid'],
			]
		);

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with('us')
			->willReturn(true);

		$this->languageMapper->expects($this->once())
			->method('isValidLanguage')
			->with('invalid')
			->willReturn(false);

		$this->termsMapper->expects($this->never())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with('us', 'invalid');

		$this->termsMapper->expects($this->never())
			->method('delete');

		$this->signatoryMapper->expects($this->never())
			->method('deleteTerm');

		$result = $this->command->run($this->inputInterface, $this->outputInterface);
		$this->assertSame(Command::FAILURE, $result);
	}

	public function testFailsForNonExistingTerms(): void {
		$this->inputInterface->method('getOption')->willReturnMap(
			[
				['country', 'us'],
				['language', 'en'],
			]
		);

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with('us')
			->willReturn(true);

		$this->languageMapper->expects($this->once())
			->method('isValidLanguage')
			->with('en')
			->willReturn(true);

		$this->termsMapper->expects($this->once())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with('us', 'en')
			->willThrowException(new TermsNotFoundException);

		$this->termsMapper->expects($this->never())
			->method('delete');

		$this->signatoryMapper->expects($this->never())
			->method('deleteTerm');

		$result = $this->command->run($this->inputInterface, $this->outputInterface);
		$this->assertSame(Command::FAILURE, $result);
	}
}

<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\TermsOfService\Tests;

use OCA\TermsOfService\Command\SetTermsCommand;
use OCA\TermsOfService\Db\Entities\Terms;
use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCA\TermsOfService\Db\Mapper\LanguageMapper;
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
final class SetTermsCommandTest extends TestCase {

	protected CountryMapper&MockObject $countryMapper;
	protected TermsMapper&MockObject $termsMapper;
	protected LanguageMapper&MockObject $languageMapper;
	protected Input&MockObject $inputInterface;
	protected ConsoleOutput&MockObject $outputInterface;
	protected SetTermsCommand $command;

	protected function setUp(): void {
		$this->countryMapper = $this->createMock(CountryMapper::class);
		$this->termsMapper = $this->createMock(TermsMapper::class);
		$this->languageMapper = $this->createMock(LanguageMapper::class);
		$this->inputInterface = $this->createMock(Input::class);
		$this->outputInterface = $this->createMock(ConsoleOutput::class);

		$this->command = new SetTermsCommand(
			$this->termsMapper,
			$this->countryMapper,
			$this->languageMapper,
		);
	}

	public function testUpdatesExistingTerms(): void {
		$this->inputInterface->method('getOption')->willReturnMap(
			[
				['country', 'us'],
				['language', 'en'],
			]
		);
		$this->inputInterface->method('getArgument')->willReturn('text', 'some random text');

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with('us')
			->willReturn(true);

		$this->languageMapper->expects($this->once())
			->method('isValidLanguage')
			->with('en')
			->willReturn(true);

		$terms = new Terms();
		$terms->setId(1);

		$this->termsMapper->expects($this->once())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with('us', 'en')
			->willReturn($terms);

		$this->termsMapper->expects($this->once())
			->method('update')
			->with($terms);

		$result = $this->command->run($this->inputInterface, $this->outputInterface);
		$this->assertSame(Command::SUCCESS, $result);
	}

	public function testCreatesNewTerms(): void {
		$this->inputInterface->method('getOption')->willReturnMap(
			[
				['country', 'us'],
				['language', 'en'],
			]
		);
		$this->inputInterface->method('getArgument')->willReturn('text', 'some random text');

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with('us')
			->willReturn(true);

		$this->languageMapper->expects($this->once())
			->method('isValidLanguage')
			->with('en')
			->willReturn(true);

		$terms = new Terms();
		$terms->setId(null);

		$this->termsMapper->expects($this->once())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with('us', 'en')
			->willThrowException(new TermsNotFoundException);

		$this->termsMapper->expects($this->once())
			->method('insert');

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
		$this->inputInterface->method('getArgument')->with('text')->willReturn('some random text');

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with(CountryMapper::GLOBAL)
			->willReturn(true);

		$this->languageMapper->expects($this->once())
			->method('isValidLanguage')
			->with('en')
			->willReturn(true);

		$terms = new Terms();
		$terms->setId(1);

		$this->termsMapper->expects($this->once())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with(CountryMapper::GLOBAL, 'en')
			->willReturn($terms);

		$this->termsMapper->expects($this->once())
			->method('update')
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
		$this->inputInterface->method('getArgument')->with('text')->willReturn('some random text');

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
		$this->inputInterface->method('getArgument')->with('text')->willReturn('some random text');

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

		$result = $this->command->run($this->inputInterface, $this->outputInterface);
		$this->assertSame(Command::FAILURE, $result);
	}

	public function testRejectsInvalidBody(): void {
		$this->inputInterface->method('getOption')->willReturnMap(
			[
				['country', 'us'],
				['language', 'en'],
			]
		);
		$this->inputInterface->method('getArgument')->with('text')->willReturn('');

		$this->countryMapper->expects($this->once())
			->method('isValidCountry')
			->with('us')
			->willReturn(true);

		$this->languageMapper->expects($this->once())
			->method('isValidLanguage')
			->with('en')
			->willReturn(true);

		$this->termsMapper->expects($this->never())
			->method('getTermsForCountryCodeAndLanguageCode')
			->with('us', 'en');

		$result = $this->command->run($this->inputInterface, $this->outputInterface);
		$this->assertSame(Command::FAILURE, $result);
	}
}

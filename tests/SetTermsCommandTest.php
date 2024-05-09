<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Tests;

use OCA\TermsOfService\Db\Entities\Terms;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\ConsoleOutput;
use OCA\TermsOfService\Command\SetTermsCommand;
use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCA\TermsOfService\Db\Mapper\LanguageMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCA\TermsOfService\Exceptions\TermsNotFoundException;

/**
 * @group Command
 */
class SetTermsCommandTest extends TestCase {

    /** @var CountryMapper|MockObject */
    protected $countryMapper;

    /** @var TermsMapper|MockObject */
    protected $termsMapper;

    /** @var LanguageMapper|MockObject */
    protected $languageMapper;

    /** @var Input|MockObject */
    protected $inputInterface;

    /** @var ConsoleOutput|MockObject */
    protected $outputInterface;

    /** @var SetTermsCommand */
    protected $command;

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

    public function testUpdatesExistingTerms() {
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
        $this->assertSame($result, Command::SUCCESS);
    }

    public function testCreatesNewTerms() {
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
        $this->assertSame($result, Command::SUCCESS);
    }

    public function testFallsBackToGlobalCountry() {
        $this->inputInterface->method('getOption')->willReturnMap(
            [
                ['country', Null],
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
        $this->assertSame($result, Command::SUCCESS);
    }

    public function testRejectsInvalidCountry() {
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
        $this->assertSame($result, Command::FAILURE);
    }

    public function testRejectsInvalidLanguage() {
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
        $this->assertSame($result, Command::FAILURE);
    }

    public function testRejectsInvalidBody() {
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
        $this->assertSame($result, Command::FAILURE);
    }
}

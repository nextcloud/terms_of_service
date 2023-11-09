<?php
/**
 * @copyright Copyright (c) 2023, Marius David Wieschollek (github.public@mdns.eu)
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
class DeleteTermsCommandTest extends TestCase {

    /** @var CountryMapper|MockObject */
    protected $countryMapper;

    /** @var TermsMapper|MockObject */
    protected $termsMapper;

    /** @var LanguageMapper|MockObject */
    protected $languageMapper;

    /** @var SignatoryMapper|MockObject */
    protected $signatoryMapper;

    /** @var Input|MockObject */
    protected $inputInterface;

    /** @var ConsoleOutput|MockObject */
    protected $outputInterface;

    /** @var DeleteTermsCommand */
    protected $command;

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

    public function testDeletesExistingTerms() {
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
        $this->assertSame($result, Command::SUCCESS);
    }

    public function testFallsBackToGlobalCountry() {
        $this->inputInterface->method('getOption')->willReturnMap(
            [
                ['country', Null],
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
        $this->assertSame($result, Command::SUCCESS);
    }

    public function testRejectsInvalidCountry() {
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
        $this->assertSame($result, Command::FAILURE);
    }

    public function testRejectsInvalidLanguage() {
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
        $this->assertSame($result, Command::FAILURE);
    }

    public function testFailsForNonExistingTerms() {
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
        $this->assertSame($result, Command::FAILURE);
    }
}
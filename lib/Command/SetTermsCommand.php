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

namespace OCA\TermsOfService\Command;

use OCA\Passwords\Exception\Command\NonInteractiveShellException;
use OCA\TermsOfService\Db\Entities\Terms;
use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCA\TermsOfService\Db\Mapper\LanguageMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCA\TermsOfService\Exceptions\TermsNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetTermsCommand extends Command {

    public function __construct(
        protected TermsMapper    $termsMapper,
        protected CountryMapper  $countryMapper,
        protected LanguageMapper $languageMapper,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('terms_of_service:term:set')
             ->addOption('country', 'c', InputOption::VALUE_OPTIONAL, 'The country code for the tos. Global if none given')
             ->addOption('language', 'l', InputOption::VALUE_REQUIRED, 'The language code for the tos')
             ->addArgument('text', InputArgument::REQUIRED, 'The text of the tos')
             ->setDescription('Create or update a tos for the given country and language');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $countryCode  = $input->getOption('country');
        $languageCode = $input->getOption('language');
        $body         = $input->getArgument('text');

        if($countryCode === null) {
            $countryCode = CountryMapper::GLOBAL;
        }

        if(!$this->countryMapper->isValidCountry($countryCode)) {
            $output->writeln("The given country is invalid");

            return Command::FAILURE;
        }

        if(!$this->languageMapper->isValidLanguage($languageCode)) {
            $output->writeln("The given language is invalid");

            return Command::FAILURE;
        }

        if(empty($body)) {
            $output->writeln("No text given. If you want to delete the tos, use terms_of_service:term:delete");

            return Command::FAILURE;
        }

        $this->saveTos($countryCode, $languageCode, $body);
        $output->writeln(sprintf("TOS for %s %s have been updated", $countryCode, $languageCode));

        return Command::SUCCESS;
    }

    protected function saveTos(string $countryCode, string $languageCode, string $body): void {
        try {
            $terms = $this->termsMapper->getTermsForCountryCodeAndLanguageCode($countryCode, $languageCode);
        } catch(TermsNotFoundException $e) {
            $terms = new Terms();
        }

        /**
         * Replace escaped new line statements with working ones
         */
        $body = str_replace('\\n', "\n", $body);

        $terms->setCountryCode($countryCode);
        $terms->setLanguageCode($languageCode);
        $terms->setBody($body);

        if(!empty($terms->getId())) {
            $this->termsMapper->update($terms);
        } else {
            $this->termsMapper->insert($terms);
        }
    }
}
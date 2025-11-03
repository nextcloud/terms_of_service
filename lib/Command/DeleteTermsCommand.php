<?php

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Command;

use OCA\TermsOfService\Db\Mapper\CountryMapper;
use OCA\TermsOfService\Db\Mapper\LanguageMapper;
use OCA\TermsOfService\Db\Mapper\SignatoryMapper;
use OCA\TermsOfService\Db\Mapper\TermsMapper;
use OCA\TermsOfService\Exceptions\TermsNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteTermsCommand extends Command {

	public function __construct(
		protected TermsMapper $termsMapper,
		protected CountryMapper $countryMapper,
		protected LanguageMapper $languageMapper,
		protected SignatoryMapper $signatoryMapper,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('terms_of_service:term:delete')
			->addOption('country', 'c', InputOption::VALUE_OPTIONAL, 'The country code for the tos. Global if none given')
			->addOption('language', 'l', InputOption::VALUE_REQUIRED, 'The language code for the tos')
			->setDescription('Delete tos for the given country and language');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$countryCode = $input->getOption('country');
		$languageCode = $input->getOption('language');

		if ($countryCode === null) {
			$countryCode = CountryMapper::GLOBAL;
		}

		if (!$this->countryMapper->isValidCountry($countryCode)) {
			$output->writeln('The given country is invalid');

			return Command::FAILURE;
		}

		if (!$this->languageMapper->isValidLanguage($languageCode)) {
			$output->writeln('The given language is invalid');

			return Command::FAILURE;
		}

		if ($this->deleteTos($countryCode, $languageCode)) {
			$output->writeln(sprintf('TOS for %s %s have been deleted', $countryCode, $languageCode));
			return Command::SUCCESS;
		}


		$output->writeln(sprintf('TOS for %s %s could not be deleted', $countryCode, $languageCode));
		return Command::FAILURE;
	}

	protected function deleteTos(string $countryCode, string $languageCode): bool {
		try {
			$terms = $this->termsMapper->getTermsForCountryCodeAndLanguageCode($countryCode, $languageCode);
		} catch (TermsNotFoundException) {
			return false;
		}

		$this->termsMapper->delete($terms);
		$this->signatoryMapper->deleteTerm($terms);

		return true;
	}
}

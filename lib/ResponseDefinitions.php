<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService;

/**
 * @psalm-type TermsOfServiceTerms = array{
 *     id: positive-int,
 *     countryCode: string,
 *     languageCode: string,
 *     body: non-empty-string,
 *     renderedBody: non-empty-string,
 * }
 *
 * @psalm-type TermsOfServiceAdminFormData = array{
 *      terms: array<string, TermsOfServiceTerms>,
 *      countries: array<string, string>,
 *      languages: array<string, string>,
 *      tos_on_public_shares: '0'|'1',
 *      tos_for_users: '0'|'1',
 *  }
 */
class ResponseDefinitions {
}

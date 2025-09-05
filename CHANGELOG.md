<!--
 - SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Changelog
All notable changes to this project will be documented in this file.

## 4.7.0
### Changed
- Migrate to outline icons

## 4.6.0
### Changed
- Updated dependencies

## 4.5.0
### Fixed
- Update openapi-extractor to v1.5.3
- Cleanup bootstrap.php to be forward-compatible
- Bump webpack-dev-server from 5.1.0 to 5.2.2

## 4.4.0
### Added
- Configuration settings to bypass checks for services:
  * `allow_ip_ranges`
  * `allow_path_prefix`

## 4.3.0
### Fixed
- Show ToS on non-files public pages (calendar/collectives/talk)
- Fix Nextcloud 31 public shares support
- Accept ToS during login flow

## 4.2.0
### Added
- Return 403 TermsNotSignedException on propfinds

### Fixed
- Fix typo in translation

## 4.1.0
### Added
- Improve save terms and reset signatures actions

## 4.0.0
### Changed
- Drop support for version 27

## 3.1.0
### Changed
- Support 27 again

## 3.0.0
### Added
- Move from index.php to ocs API
### Fixed
- Strong types to storage wrapper

## 2.5.0
### Added
- Force the user to scroll text to accept the TOS
### Fixed
- Cache the uuid if the tos is signed by the user
### Changed
- Updated translations
- Updated dependencies

## 2.4.0
### Added
- Add a listerner when new terms of service is created
### Changed
- Updated dependencies

## 2.3.2
### Added
- OCC commands to change or delete TOS
### Changed
- Added Nextcloud 28 compatibility
- Updated dependencies

## 2.3.1 – 2023-09-12
### Fixed
- Allow office online wopi requests

## 2.3.0 – 2023-09-01
### Fixed
- Add title attr to main button

## 2.2.0 – 2023-05-15
### Changed
- Added Nextcloud 27 compatibility
- Updated dependencies

## 2.1.0 – 2023-02-17
### Changed
- Added Nextcloud 26 compatibility

### Fixed
- Fixed an error when Richdocuments has an invalid allow list configured
  [#797](https://github.com/nextcloud/terms_of_service/pull/797)

## 2.0.1 – 2023-02-02
### Fixed
- Compatibility with Richdocuments app
  [#767](https://github.com/nextcloud/terms_of_service/pull/767)

## 1.10.3 – 2023-02-02
### Fixed
- Compatibility with Richdocuments app
  [#769](https://github.com/nextcloud/terms_of_service/pull/769)

## 1.9.4 – 2023-02-02
### Fixed
- Compatibility with Richdocuments app
  [#768](https://github.com/nextcloud/terms_of_service/pull/768)

## 2.0.0 – 2022-10-18
### Added
- Option to change the configuration settings in the administration interface
- Migrated the UI to Vue
- Added compatibility with Nextcloud 25

### Removed
- Removed compatibility with Nextcloud 24

## 1.10.2 – 2022-04-12
### Changed
- Compatibility with Nextcloud 24

## 1.9.3 – 2022-03-03
### Fixed
- Fix an issue with IPs which only return a continent not a country
  [#692](https://github.com/nextcloud/terms_of_service/pull/692)

## 1.9.2 – 2022-01-17
### Fixed
- Fix user_id column length
  [#666](https://github.com/nextcloud/terms_of_service/pull/666)
- Add index for performance
  [#651](https://github.com/nextcloud/terms_of_service/pull/651)

## 1.9.1 – 2021-12-13
### Fixed
- Fix popover being hidden behind the file list
  [#655](https://github.com/nextcloud/terms_of_service/pull/655)

## 1.9.0 – 2021-11-24
### Changed
- Compatibility with Nextcloud 23

## 1.8.1 – 2021-09-21
### Fixed
- Fix text color in terms of registration integration after dependency update
  [#622](https://github.com/nextcloud/terms_of_service/pull/622)

## 1.8.0 – 2021-07-06
### Changed
- Compatibility with Nextcloud 22

## 1.7.1 – 2021-07-06
### Fixed
- Fix link color in terms of registration integration
  [#602](https://github.com/nextcloud/terms_of_service/pull/602)

## 1.7.0 – 2021-04-15
### Added
- Integration into the Registration app
  [#521](https://github.com/nextcloud/terms_of_service/pull/521)
- Compatibility with Nextcloud 21
  [#509](https://github.com/nextcloud/terms_of_service/pull/509)

### Fixed
- Terms of service not working as expected for normal users when enabled on public shares
  [#503](https://github.com/nextcloud/terms_of_service/pull/503)
- Don't block writing skeleton files from LoginFlow and Registration
  [#496](https://github.com/nextcloud/terms_of_service/pull/496)

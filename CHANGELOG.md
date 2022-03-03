# Changelog
All notable changes to this project will be documented in this file.

## 1.8.5 – 2022-03-03
### Fixed
- Fix an issue with IPs which only return a continent not a country
  [#693](https://github.com/nextcloud/terms_of_service/pull/693)

## 1.8.4 – 2022-01-17
### Fixed
- Fix user_id column length 
  [#667](https://github.com/nextcloud/terms_of_service/pull/667)
- Add index for performance
  [#652](https://github.com/nextcloud/terms_of_service/pull/652)

## 1.8.1 – 2021-09-21
### Fixed
- Fix text color in terms of registration integration after dependency update
  [#622](https://github.com/nextcloud/registration/pull/622)

## 1.8.0 – 2021-07-06
### Changed
- Compatibility with Nextcloud 22

## 1.7.1 – 2021-07-06
### Fixed
- Fix link color in terms of registration integration
  [#602](https://github.com/nextcloud/registration/pull/602)

## 1.7.0 – 2021-04-15
### Added
- Integration into the Registration app
  [#521](https://github.com/nextcloud/registration/pull/521)
- Compatibility with Nextcloud 21
  [#509](https://github.com/nextcloud/registration/pull/509)

### Fixed
- Terms of service not working as expected for normal users when enabled on public shares
  [#503](https://github.com/nextcloud/registration/pull/503)
- Don't block writing skeleton files from LoginFlow and Registration
  [#496](https://github.com/nextcloud/registration/pull/496)

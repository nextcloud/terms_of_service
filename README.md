<!--
 - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# 📜 Terms of service

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/terms_of_service)](https://api.reuse.software/info/github.com/nextcloud/terms_of_service)

> ![](https://raw.githubusercontent.com/nextcloud/terms_of_service/master/docs/popup-dialog.png)

Requires users to accept terms of service before accessing data. Text and languages are configurable on the administration panel.

## 🔗 Display on public shares

The setting applies to shares via link or mail (with and without password protection).

Default is disabled: `0`
```
./occ config:app:set terms_of_service tos_on_public_shares --value '1'
```
## 👤 Exclude registered users

To exclude registered users from accepting the terms of service, set this config to `0`.
Therefore, only public link and mail sharees have to accept the terms of service.

Default is enabled: `1`
```
./occ config:app:set terms_of_service tos_for_users --value '0'
```

## 🔌 Allow access from other services

Some other services such as office suites communicate directly with the Nextcloud server.
For Nextcloud Office and Officeonline the `wopi_allowlist` settings of the respective apps are taken into account.

To allow other services to bypass the terms of service check:
* Set `allow_path_prefix` to the paths that access should be granted to.
* Set `allow_ip_ranges` to match the ip addresses of the servers in question.
  If you are using a reverse proxy, use the ip address of the application server.
  Access is allowed based on the x-forwarded-for header and not the source ip.

Default for `allow_path_prefix` is none: ``
Default for `allow_ip_ranges` is none: ``

```
./occ config:app:set terms_of_service allow_path_prefix --value '/apps/onlyoffice/download'
./occ config:app:set terms_of_service allow_ip_ranges --value '10.0.0.5,10.0.0.6'
```

## 🏗️ Development setup

1. Clone the repository
2. Setup your environment: `make`
3. Start contributing 🎉

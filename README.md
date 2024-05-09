<!--
 - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# 📜 Terms of service

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

## 🏗️ Development setup

1. Clone the repository
2. Setup your environment: `make`
3. Start contributing 🎉

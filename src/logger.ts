// SPDX-FileCopyrightText: 2025 Nextcloud GmbH
// SPDX-FileContributor: Carl Schwan
// SPDX-License-Identifier: AGPL-3.0-or-later

import { getLoggerBuilder } from '@nextcloud/logger'

export default getLoggerBuilder()
	.setApp('terms_of_services')
	.detectUser()
	.build()

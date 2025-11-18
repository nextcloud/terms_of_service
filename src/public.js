/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { getCurrentUser } from '@nextcloud/auth'
import { createApp } from 'vue'
import UserApp from './UserApp.vue'

const isNotLoggedIn = getCurrentUser() === null
const isPasswordProtected = (document.getElementById('password-submit') !== null)

if (isNotLoggedIn && !isPasswordProtected) {
	const tofc = document.createElement('div')
	tofc.id = 'terms_of_service_confirm'
	document.body.insertAdjacentElement('afterbegin', tofc)

	createApp(UserApp, {
		source: 'public',
	}).mount('#terms_of_service_confirm')
}

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import UserApp from './UserApp.vue'

const tofc = document.createElement('div')
tofc.id = 'terms_of_service_confirm'
document.body.insertAdjacentElement('afterbegin', tofc)

createApp(UserApp, {
	source: 'public',
}).mount('#terms_of_service_confirm')

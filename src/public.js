/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import { getCurrentUser } from '@nextcloud/auth'
import UserApp from './UserApp.vue'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

const isNotLoggedIn = getCurrentUser() === null
const isPasswordProtected = (document.getElementById('password-submit') !== null)

if (isNotLoggedIn && !isPasswordProtected) {
	const tofc = document.createElement('div')
	tofc.id = 'terms_of_service_confirm'
	document.body.insertAdjacentElement('afterbegin', tofc)

	// eslint-disable-next-line
	new Vue({
		el: '#terms_of_service_confirm',
		data: {
			source: 'public',
		},
		render: h => h(UserApp),
	})
}

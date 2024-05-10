/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import UserApp from './UserApp.vue'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

const hasToken = (document.getElementById('sharingToken') !== null)
const isPasswordProtected = (document.getElementById('password-submit') !== null)

if (hasToken && !isPasswordProtected) {
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

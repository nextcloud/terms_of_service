/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import UserApp from './UserApp.vue'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

const tofc = document.createElement('div')
tofc.id = 'terms_of_service_confirm'
document.body.insertAdjacentElement('afterbegin', tofc)

export default new Vue({
	el: '#terms_of_service_confirm',
	render: h => h(UserApp),
})

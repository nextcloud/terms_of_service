/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import App from './RegistrationApp.vue'

createApp(App, {
	source: 'public',
}).mount('#terms_of_service')

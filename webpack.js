/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	admin: path.join(__dirname, 'src', 'admin.js'),
	user: path.join(__dirname, 'src', 'user.js'),
	public: path.join(__dirname, 'src', 'public.js'),
	registration: path.join(__dirname, 'src', 'registration.js'),
}

module.exports = webpackConfig

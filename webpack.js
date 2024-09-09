/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
const path = require('path')
const WebpackSPDXPlugin = require('./build-js/WebpackSPDXPlugin.js')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	admin: path.join(__dirname, 'src', 'admin.js'),
	user: path.join(__dirname, 'src', 'user.js'),
	public: path.join(__dirname, 'src', 'public.js'),
	registration: path.join(__dirname, 'src', 'registration.js'),
}

webpackConfig.plugins = [
	...webpackConfig.plugins,
	// Generate reuse license files
	new WebpackSPDXPlugin({
		override: {
			// TODO: Remove if they fixed the license in the package.json
			'@nextcloud/axios': 'GPL-3.0-or-later',
			'@nextcloud/vue': 'AGPL-3.0-or-later',
			'nextcloud-vue-collections': 'AGPL-3.0-or-later',
		}
	}),
]

module.exports = webpackConfig

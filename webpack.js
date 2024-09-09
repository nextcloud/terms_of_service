/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
const path = require('path')
const WebpackSPDXPlugin = require('./build-js/WebpackSPDXPlugin.js')
const webpackConfig = require('@nextcloud/webpack-vue-config')

const isDev = process.env.NODE_ENV === 'development'

webpackConfig.entry = {
	admin: path.join(__dirname, 'src', 'admin.js'),
	user: path.join(__dirname, 'src', 'user.js'),
	public: path.join(__dirname, 'src', 'public.js'),
	registration: path.join(__dirname, 'src', 'registration.js'),
}

// Generate reuse license files if not in development mode
if (!isDev) {
	webpackConfig.plugins.push(new WebpackSPDXPlugin({
		override: {
			select2: 'MIT',
			'@nextcloud/axios': 'GPL-3.0-or-later',
			'@nextcloud/vue': 'AGPL-3.0-or-later',
			'nextcloud-vue-collections': 'AGPL-3.0-or-later',
		},
	}))

	webpackConfig.optimization.minimizer = [{
		apply: (compiler) => {
			// Lazy load the Terser plugin
			const TerserPlugin = require('terser-webpack-plugin')
			new TerserPlugin({
				extractComments: false,
				terserOptions: {
					format: {
						comments: false,
					},
					compress: {
						passes: 2,
					},
				},
		  }).apply(compiler)
		},
	}]
}

module.exports = webpackConfig

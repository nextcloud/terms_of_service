const { merge } = require('webpack-merge')
const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

const config = {
	entry: {
		admin: path.join(__dirname, 'src', 'admin.js'),
		user: path.join(__dirname, 'src', 'user.js'),
		public: path.join(__dirname, 'src', 'public.js'),
		registration: path.join(__dirname, 'src', 'registration.js'),
	},
	output: {
		filename: 'terms_of_service_[name].js',
	},
}

const mergedConfigs = merge(webpackConfig, config)
delete mergedConfigs.entry.main
module.exports = mergedConfigs

const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

delete webpackConfig.entry.main
webpackConfig.entry.admin = path.join(__dirname, 'src', 'admin.js')
webpackConfig.entry.user = path.join(__dirname, 'src', 'user.js')
webpackConfig.entry.public = path.join(__dirname, 'src', 'public.js')
webpackConfig.entry.registration = path.join(__dirname, 'src', 'registration.js')
webpackConfig.output.filename = 'terms_of_service_[name].js'

module.exports = webpackConfig

# Makefile for building the project

app_name=terms_of_service

project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
sign_dir=$(build_dir)/sign
package_name=$(app_name)
cert_dir=$(HOME)/.nextcloud/certificates
version+=master

all: dev-setup build-js-production

dev-setup: clean clean-dev npm-init

npm-init:
	npm install

npm-update:
	npm update

build-js:
	npm run dev

build-js-production:
	npm run build

watch-js:
	npm run watch

clean:
	rm -f js/terms_of_service_admin.js
	rm -f js/terms_of_service_admin.js.map
	rm -f js/terms_of_service_user.js
	rm -f js/terms_of_service_user.js.map

clean-dev:
	rm -rf $(build_dir)
	rm -rf node_modules

release: appstore create-tag

create-tag:
	git tag -a v$(version) -m "Tagging the $(version) release."
	git push origin v$(version)

appstore: clean clean-dev npm-init build-js-production
	mkdir -p $(sign_dir)
	rsync -a \
	--exclude=.babelrc \
	--exclude=bower.json \
	--exclude=.bowerrc \
	--exclude=/build \
	--exclude=composer.json \
	--exclude=docs \
	--exclude=.drone.yml \
	--exclude=.editorconfig \
	--exclude=.eslintignore \
	--exclude=.eslintrc.yml \
	--exclude=.git \
	--exclude=.gitattributes \
	--exclude=.github \
	--exclude=.gitignore \
	--exclude=.jscsrc \
	--exclude=.jshintrc \
	--exclude=.jshintignore \
	--exclude=.l10nignore \
	--exclude=js/tests \
	--exclude=karma.conf.js \
	--exclude=l10n/no-php \
	--exclude=.tx \
	--exclude=Makefile \
	--exclude=node_modules \
	--exclude=package.json \
	--exclude=phpunit*xml \
	--exclude=README.md \
	--exclude=run-*lint.sh \
	--exclude=.scrutinizer.yml \
	--exclude=.stylelintrc \
	--exclude=/src \
	--exclude=tests \
	--exclude=.travis.yml \
	--exclude=webpack.*.js \
	$(project_dir)/  $(sign_dir)/$(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing app files…"; \
		php ../../occ integrity:sign-app \
			--privateKey=$(cert_dir)/$(app_name).key\
			--certificate=$(cert_dir)/$(app_name).crt\
			--path=$(sign_dir)/$(app_name); \
	fi
	tar -czf $(build_dir)/$(app_name)-$(version).tar.gz \
		-C $(sign_dir) $(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing package…"; \
		openssl dgst -sha512 -sign $(cert_dir)/$(app_name).key $(build_dir)/$(app_name)-$(version).tar.gz | openssl base64; \
	fi

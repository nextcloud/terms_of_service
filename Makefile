# SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: AGPL-3.0-or-later
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

dev-setup: clean clean-dev composer-init npm-init

composer-init:
	composer install

npm-init:
	npm ci

npm-update:
	npm update

build-js:
	npm run dev

build-js-production:
	npm run build

watch-js:
	npm run watch

clean:
	rm -rf js

clean-dev:
	rm -rf $(build_dir)
	rm -rf node_modules
	rm -rf vendor

release: appstore create-tag

create-tag:
	git tag -a v$(version) -m "Tagging the $(version) release."
	git push origin v$(version)

appstore:
	mkdir -p $(sign_dir)
	rsync -a \
	--exclude=.eslintrc.js \
	--exclude=.git \
	--exclude=.gitattributes \
	--exclude=.gitignore \
	--exclude=.l10nignore \
	--exclude=.php-cs-fixer.cache \
	--exclude=.php-cs-fixer.dist.php \
	--exclude=/.github \
	--exclude=/.tx \
	--exclude=/build \
	--exclude=/build-js \
	--exclude=/docs \
	--exclude=/node_modules \
	--exclude=/src \
	--exclude=/tests \
	--exclude=/vendor \
	--exclude=/vendor-bin \
	--exclude=Makefile \
	--exclude=README.md \
	--exclude=babel.config.js \
	--exclude=composer.json \
	--exclude=composer.lock \
	--exclude=eslint.config.mjs \
	--exclude=l10n/no-php \
	--exclude=package.json \
	--exclude=psalm.xml \
	--exclude=rector.php \
	--exclude=stylelint.config.cjs \
	--exclude=tsconfig.json \
	--exclude=vite.config.ts \
	--exclude=webpack.js \
	$(project_dir)/  $(sign_dir)/$(app_name)
	# Install production-only composer dependencies into the package
	cp $(project_dir)/composer.json $(project_dir)/composer.lock $(sign_dir)/$(app_name)/
	composer install --working-dir=$(sign_dir)/$(app_name) \
		--no-dev --no-scripts --no-interaction --optimize-autoloader
	rm -f $(sign_dir)/$(app_name)/composer.json $(sign_dir)/$(app_name)/composer.lock
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing app files…"; \
		php ../../occ integrity:sign-app \
			--privateKey=$(cert_dir)/$(app_name).key\
			--certificate=$(cert_dir)/$(app_name).crt\
			--path=$(sign_dir)/$(app_name); \
	fi
	tar -czf $(build_dir)/$(app_name).tar.gz \
		-C $(sign_dir) $(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing package…"; \
		openssl dgst -sha512 -sign $(cert_dir)/$(app_name).key $(build_dir)/$(app_name).tar.gz | openssl base64; \
	fi

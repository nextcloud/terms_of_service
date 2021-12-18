<!--
 - @copyright Copyright (c) 2021 Marco Ambrosini <marcoambrosini@pm.me>
 - @copyright Copyright (c) 2021 Nikola Gladovic <nikola.gladovic@nextcloud.com>

 -
 - @author Marco Ambrosini <marcoambrosini@pm.me>
 - @author Nikola Gladovic <nikola.gladovic@nextcloud.com>
 -
 - @license GNU AGPL version 3 or any later version
 -
 - This program is free software: you can redistribute it and/or modify
 - it under the terms of the GNU Affero General Public License as
 - published by the Free Software Foundation, either version 3 of the
 - License, or (at your option) any later version.
 -
 - This program is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 - GNU Affero General Public License for more details.
 -
 - You should have received a copy of the GNU Affero General Public License
 - along with this program. If not, see <http://www.gnu.org/licenses/>.
 -
 -->

<template>
	<div id="terms_of_service_content" class="modal-content">
		<!-- Sticky Header -->
		<div class="modal-content__header">
			<slot name="header" />
		</div>

		<!-- Scrollable terms of service -->
		<slot />

		<!-- Sticky button -->
		<button class="primary modal-content__button"
			@click.prevent.stop="handleClick">
			{{ t('terms_of_service', 'I acknowledge that I have read and agree to the above terms of service') }}
		</button>
	</div>
</template>

<script>
export default {
	name: 'ModalContent',

	methods: {
		handleClick() {
			this.$emit('click')
		},
	},
}
</script>

<style lang="scss" scoped>
/* Little hack to strengthen the css selector so links with dark mode on the registration page are readable */
#terms_of_service_content.modal-content,
.modal-content {
	padding: 0 12px;
	height: 100%;
	display: flex;
	flex-direction: column;
	color: var(--color-main-text);

	&__header {
		padding-top: 12px;
	}

	h3 {
		float: left;
		font-weight: 800;
	}

	&__button {
		margin: 8px 0 12px 0;
	}

	select {
		float: right;
		padding: 0 12px;

		/**
		 * Need to overwrite the rules of guest.css
		 */
		color: var(--color-main-text);
		background: var(--icon-triangle-s-000) no-repeat right 4px center;
		border: 1px solid var(--color-border-dark);
		border-radius: var(--border-radius);
		&:hover {
			border-color: var(--color-primary-element);
		}
	}

	/**
	 * Basic Markdown support
	 */
	::v-deep div.text-content {
		height: 100%;
		overflow: auto;
		text-align: left;

		h3 {
			font-weight: 800;
		}

		p {
			padding-top: 5px;
			padding-bottom: 5px;
		}

		ol {
			padding-left: 12px;
		}

		ul {
			list-style-type: disc;
			padding-left: 25px;
		}

		a {
			text-decoration: underline;
			color: var(--color-main-text) !important;
		}
	}
}

</style>

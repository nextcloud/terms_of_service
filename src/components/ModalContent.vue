<!--
  - SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div
		id="terms_of_service_content"
		class="modal-content"
		aria-live="polite">
		<!-- Sticky Header -->
		<div class="modal-content__header">
			<slot name="header" />
		</div>

		<!-- Scrollable terms of service -->
		<div ref="termsContent" class="terms-content">
			<slot />
		</div>

		<!-- Sticky button -->
		<NcButton
			ref="acceptButton"
			class="modal-content__button"
			variant="primary"
			:wide="true"
			autofocus
			:title="t('terms_of_service', 'I acknowledge that I have read and agree to the above terms of service')"
			:disabled="!isScrollComplete"
			@click.prevent.stop="handleClick"
			@keydown.enter="handleClick">
			{{ t('terms_of_service', 'I acknowledge that I have read and agree to the above terms of service') }}
		</NcButton>
	</div>
</template>

<script>
import { t } from '@nextcloud/l10n'
import NcButton from '@nextcloud/vue/components/NcButton'

export default {
	name: 'ModalContent',

	components: {
		NcButton,
	},

	emits: ['click'],

	props: {
		isScrollComplete: {
			type: Boolean,
			default: false,
		},
	},

	mounted() {
		this.$nextTick(() => {
			this.$refs.acceptButton.$el.focus()
		})
	},

	methods: {
		handleClick() {
			if (this.isScrollComplete) {
				this.$emit('click')
			}
		},

		t,
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
		float: inline-start;
		font-weight: 800;
	}

	&__button {
		margin: 8px 0 12px 0;
	}

	.terms-content {
		height: 100%;
		overflow-y: auto;
		flex: 1;
	}

	select {
		float: inline-end;
		padding: 0 12px;
		/**
		 * Need to overwrite the rules of guest.css
		 */
		color: var(--color-main-text);
		border: 1px solid var(--color-border-dark);
		border-radius: var(--border-radius);
		&:hover {
			border-color: var(--color-primary-element);
		}
	}
	/**
	 * Basic Markdown support
	 */
	:deep(div.text-content) {
		height: 100%;
		overflow: auto;
		text-align: start;

		h3 {
			font-weight: 800;
		}

		p {
			padding-top: 5px;
			padding-bottom: 5px;
		}

		ol {
			padding-inline-start: 12px;
		}

		ul {
			list-style-type: disc;
			padding-inline-start: 25px;
		}

		a {
			text-decoration: underline;
			color: var(--color-main-text) !important;
		}
	}
}
</style>

<style lang="scss" scoped>
:deep(.modal-container) {
	display: flex;
	height: 100%;
}
</style>

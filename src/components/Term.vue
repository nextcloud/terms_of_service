<!--
 - SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<li class="terms-row">
		<span class="terms-row__label">
			{{ country }} ({{ language }})
		</span>

		<NcButton :aria-label="editButtonLabel"
			type="tertiary"
			@click="onEdit">
			<template #icon>
				<IconPencil :size="20" />
			</template>
		</NcButton>

		<NcButton :disabled="deleteButtonDisabled"
			type="tertiary"
			:aria-label="deleteButtonLabel"
			@click="onDelete">
			<template #icon>
				<IconDelete :size="20" />
			</template>
		</NcButton>
	</li>
</template>

<script>
import axios from '@nextcloud/axios'
import IconDelete from 'vue-material-design-icons/Delete.vue'
import IconPencil from 'vue-material-design-icons/Pencil.vue'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'Term',

	components: {
		IconDelete,
		IconPencil,
		NcButton,
	},

	props: {
		id: {
			type: Number,
			required: true,
		},
		countryCode: {
			type: String,
			required: true,
		},
		languageCode: {
			type: String,
			required: true,
		},
		body: {
			type: String,
			required: true,
		},
		renderedBody: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			deleteButtonDisabled: false,
		}
	},

	computed: {
		country() {
			return this.$parent.$parent.$parent.countries[this.countryCode]
		},

		language() {
			return this.$parent.$parent.$parent.languages[this.languageCode]
		},

		editButtonLabel() {
			return t('terms_of_service', 'Edit language {language} for region {country}', { language: this.language, country: this.country })
		},

		deleteButtonLabel() {
			if (this.deleteButtonDisabled) {
				return t('terms_of_service', 'Deleting …')
			}

			return t('terms_of_service', 'Delete language {language} for region {country}', { language: this.language, country: this.country })
		},
	},

	methods: {
		onEdit() {
			this.$parent.$parent.$parent.country = {
				value: this.countryCode,
				label: this.$parent.$parent.$parent.countries[this.countryCode] + ' (' + this.countryCode + ')',
			}
			this.$parent.$parent.$parent.language = {
				value: this.languageCode,
				label: this.$parent.$parent.$parent.languages[this.languageCode] + ' (' + this.languageCode + ')',
			}
			this.$parent.$parent.$parent.body = this.body
		},

		onDelete() {
			this.deleteButtonDisabled = true
			axios
				.delete(generateUrl('/apps/terms_of_service/terms/' + this.id))
				.then(() => {
					this.$delete(this.$parent.$parent.$parent.terms, this.id)
				})
		},
	},
}
</script>

<style lang="scss" scoped>
.terms-row {
	display: flex;

	&__label {
		line-height: 44px;
	}
}
</style>

<!--
 - SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<li class="terms-row">
		<span class="terms-row__label">
			{{ country }} ({{ language }})
		</span>

		<NcButton
			:aria-label="editButtonLabel"
			variant="tertiary"
			@click="onEdit">
			<template #icon>
				<IconPencil :size="20" />
			</template>
		</NcButton>

		<NcButton
			:disabled="deleteButtonDisabled"
			variant="tertiary"
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
import { generateOcsUrl } from '@nextcloud/router'
import NcButton from '@nextcloud/vue/components/NcButton'
import IconPencil from 'vue-material-design-icons/PencilOutline.vue'
import IconDelete from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'TermRow',

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

		countries: {
			type: Object,
			required: true,
		},

		languages: {
			type: Object,
			required: true,
		},
	},

	emits: ['deleted', 'edited'],

	data() {
		return {
			deleteButtonDisabled: false,
		}
	},

	computed: {
		country() {
			return this.countries[this.countryCode]
		},

		language() {
			return this.languages[this.languageCode]
		},

		editButtonLabel() {
			return t('terms_of_service', 'Edit language {language} for region {country}', { language: this.language, country: this.country })
		},

		deleteButtonLabel() {
			if (this.deleteButtonDisabled) {
				return t('terms_of_service', 'Deleting …')
			}

			return t('terms_of_service', 'Delete language {language} for region {country}', { language: this.language, country: this.country })
		},
	},

	methods: {
		onEdit() {
			this.$emit('edited', {
				countryCode: this.countryCode,
				languageCode: this.languageCode,
				body: this.body,
			})
		},

		onDelete() {
			this.deleteButtonDisabled = true
			axios
				.delete(generateOcsUrl('/apps/terms_of_service/terms/' + this.id))
				.then(() => {
					this.$emit('deleted', this.id)
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

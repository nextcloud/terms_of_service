<template>
	<li>
		{{ country }} ({{ language }})
		<button @click="onEdit">
			{{ t('terms_of_service', 'Edit') }}
		</button>
		<button :disabled="deleteButtonDisabled" @click="onDelete">
			{{ deleteButtonText }}
		</button>
	</li>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'Term',

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
			deleteButtonText: t('terms_of_service', 'Delete'),
			deleteButtonDisabled: false,
		}
	},

	computed: {
		country() {
			return this.$parent.countries[this.countryCode]
		},
		language() {
			return this.$parent.languages[this.languageCode]
		},
	},

	methods: {
		onEdit: function() {
			this.$parent.country = {
				value: this.countryCode,
				label: this.$parent.countries[this.countryCode] + ' (' + this.countryCode + ')',
			}
			this.$parent.language = {
				value: this.languageCode,
				label: this.$parent.languages[this.languageCode] + ' (' + this.languageCode + ')',
			}
			this.$parent.body = this.body
		},
		onDelete: function() {
			this.deleteButtonDisabled = true
			this.deleteButtonText = t('terms_of_service', 'Deleting …')
			axios
				.delete(generateUrl('/apps/terms_of_service/terms/' + this.id))
				.then(() => {
					this.$delete(this.$parent.terms, this.id)
				})
		},
	},
}
</script>

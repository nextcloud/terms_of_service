<template>
	<li>
		{{country}} ({{language}})
		<button @click="onEdit">{{ t('terms_of_service', 'Edit') }}</button>
		<button @click="onDelete" :disabled="deleteButtonDisabled">{{deleteButtonText}}</button>
	</li>
</template>

<script>
	import axios from 'axios';

	export default {
		name: 'term',

		props: [
			'id',
			'countryCode',
			'languageCode',
			'body',
			'renderedBody'
		],

		data () {
			return {
				deleteButtonText: t('terms_of_service', 'Delete'),
				deleteButtonDisabled: false
			};
		},

		computed: {
			country () {
				return this.$parent.countries[this.countryCode];
			},
			language () {
				return this.$parent.languages[this.languageCode];
			},
			tokenHeaders () {
				return { headers: { requesttoken: OC.requestToken } };
			}
		},

		methods: {
			onEdit: function() {
				this.$parent.country = {
					value: this.countryCode,
					label: this.$parent.countries[this.countryCode] + ' (' + this.countryCode + ')'
				};
				this.$parent.language = {
					value: this.languageCode,
					label: this.$parent.languages[this.languageCode] + ' (' + this.languageCode + ')'
				};
				this.$parent.body = this.body;
			},
			onDelete: function() {
				this.deleteButtonDisabled = true;
				this.deleteButtonText = t('terms_of_service', 'Deleting …');
				axios
					.delete(OC.generateUrl('/apps/terms_of_service/terms/' + this.id), this.tokenHeaders)
					.then(response => {
						this.$delete(this.$parent.terms, this.id);
					});
			}
		}
	}
</script>

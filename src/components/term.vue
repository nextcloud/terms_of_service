<template>
	<li>
		{{country}} ({{language}})
		<button @click="onEdit">{{ t('termsandconditions', 'Edit') }}</button>
		<button @click="onDelete">{{ t('termsandconditions', 'Delete') }}</button>
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
				this.$parent.country = this.countryCode;
				this.$parent.language = this.languageCode;
				this.$parent.body = this.body;
			},
			onDelete: function() {
				axios
					.delete(OC.generateUrl('/apps/termsandconditions/terms/' + this.id), this.tokenHeaders)
					.then(response => {
						this.$delete(this.$parent.terms, this.id);
					});
			}
		}
	}
</script>

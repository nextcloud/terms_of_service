<template>
	<li>
		{{country}} ({{language}})
		<button @click="onEdit">{{ t('termsandconditions', 'Edit') }}</button>
		<button @click="onDelete">{{ t('termsandconditions', 'Delete') }}</button>
	</li>
</template>

<script>
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
			}
		},

		methods: {
			onEdit: function() {
				this.$parent.country = this.countryCode;
				this.$parent.language = this.languageCode;
				this.$parent.body = this.body;
			},
			onDelete: function() {
				$.ajax({
					url: OC.generateUrl('/apps/termsandconditions/terms/' + this.id),
					type: 'DELETE',
					success: function () {
						this._$el.fadeOut(OC.menuSpeed);
						this.$emit('remove');
					}.bind(this)
				});
			}
		}
	}
</script>

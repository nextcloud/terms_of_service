<!--
 - @copyright Copyright (c) 2018 Joas Schilling <coding@schilljs.com>
 - @copyright Copyright (c) 2019 Gary Kim <gary@garykim.dev>
 -
 - @author Joas Schilling <coding@schilljs.com>
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
	<NcSettingsSection :title="t('terms_of_service', 'Terms of service')"
		:description="t('terms_of_service', 'Require users to accept the terms of service before accessing the service.')">
		<p class="settings-hint">
			{{ t('terms_of_service', 'For formatting purposes Markdown is supported.') }}
		</p>

		<span>
			<NcMultiselect v-model="country"
				:options="countryOptions"
				:placeholder="t('terms_of_service', 'Select a region')"
				label="label"
				track-by="value" />
			<NcMultiselect v-model="language"
				:options="languageOptions"
				:placeholder="t('terms_of_service', 'Select a language')"
				label="label"
				track-by="value" />
		</span>

		<textarea v-model="body"
			:placeholder="t('terms_of_service', 'By using this service …')"
			class="terms__textarea" />

		<NcButton :disabled="saveButtonDisabled"
			@click="onSubmit">
			{{ saveButtonText }}
		</NcButton>

		<template v-if="hasTerms">
			<h2>
				{{ t('terms_of_service', 'Existing terms of service') }}
			</h2>

			<NcButton :disabled="resetButtonDisabled"
				type="error"
				@click="onResetSignatories">
				{{ resetButtonText }}
			</NcButton>

			<ul v-if="hasTerms" id="terms_of_service-countryspecific-list">
				<Term v-for="term in terms"
					:key="term.id"
					v-bind="term" />
			</ul>
		</template>
	</NcSettingsSection>
</template>

<script>
import Term from './components/Term.vue'
import axios from '@nextcloud/axios'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcMultiselect from '@nextcloud/vue/dist/Components/NcMultiselect.js'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import '@nextcloud/dialogs/styles/toast.scss'

export default {
	name: 'App',

	components: {
		Term,
		NcButton,
		NcMultiselect,
		NcSettingsSection,
	},

	data() {
		return {
			country: '',
			language: '',
			body: '',
			countries: {},
			countryOptions: [],
			languages: {},
			languageOptions: [],
			terms: {},
			saveButtonText: '',
			saveButtonDisabled: true,
			resetButtonText: '',
			resetButtonDisabled: false,
		}
	},

	computed: {
		hasTerms() {
			return Object.keys(this.terms).length > 0
		},
	},

	mounted() {
		this.saveButtonText = t('terms_of_service', 'Loading …')
		this.resetButtonText = t('terms_of_service', 'Reset all signatories')
		axios
			.get(generateUrl('/apps/terms_of_service/terms/admin'))
			.then(response => {
				if (response.data.terms.length !== 0) {
					this.terms = response.data.terms
				}
				this.countries = response.data.countries
				this.languages = response.data.languages
				Object.keys(this.countries).forEach((countryCode) => {
					this.countryOptions.push({
						value: countryCode,
						label: this.countries[countryCode] + ' (' + countryCode + ')',
					})
				})
				Object.keys(this.languages).forEach((languageCode) => {
					this.languageOptions.push({
						value: languageCode,
						label: this.languages[languageCode] + ' (' + languageCode + ')',
					})
				})

				this.saveButtonText = t('terms_of_service', 'Save')
				this.saveButtonDisabled = false
			})
	},

	methods: {
		onSubmit() {
			if (!this.country || !this.language || !this.body) {
				showError(t('terms_of_service', 'Ensure that all fields are filled'))
				return
			}

			this.saveButtonDisabled = true

			axios
				.post(generateUrl('/apps/terms_of_service/terms'),
					{
						countryCode: this.country.value,
						languageCode: this.language.value,
						body: this.body,
					})
				.then(response => {
					this.$set(this.terms, response.data.id, response.data)

					showSuccess(t('terms_of_service', 'Terms saved successfully!'))
					this.saveButtonDisabled = false
				})
		},
		onResetSignatories() {
			this.resetButtonDisabled = true

			axios
				.delete(generateUrl('/apps/terms_of_service/sign'))
				.then(() => {
					showSuccess(t('terms_of_service', 'All signatories reset!'))
					this.resetButtonDisabled = false
				})
		},
	},
}
</script>

<style lang="scss" scoped>
.terms__textarea {
	width: 100%;
	display: block;
}

label span {
	display: inline-block;
	min-width: 120px;
	padding: 8px 0;
	vertical-align: top;
}

</style>

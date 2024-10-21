<!--
  - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<Fragment>
		<NcSettingsSection :name="t('terms_of_service', 'Terms of service')"
			:description="t('terms_of_service', 'Require users to accept the terms of service before accessing the service.')">
			<NcCheckboxRadioSwitch type="switch"
				:checked.sync="showForLoggedInUser">
				{{ t('terms_of_service', 'Show for logged-in users') }}
			</NcCheckboxRadioSwitch>

			<NcCheckboxRadioSwitch type="switch"
				:checked.sync="showOnPublicShares">
				{{ t('terms_of_service', 'Show on public shares') }}
			</NcCheckboxRadioSwitch>

			<span class="form">
				<NcSelect v-model="country"
					:options="countryOptions"
					:placeholder="t('terms_of_service', 'Select a region')"
					:aria-label-combobox="t('terms_of_service', 'Select a region')"
					label="label"
					track-by="value" />
				<NcSelect v-model="language"
					:options="languageOptions"
					:placeholder="t('terms_of_service', 'Select a language')"
					:aria-label-combobox="t('terms_of_service', 'Select a language')"
					label="label"
					track-by="value" />
			</span>

			<textarea v-model="body"
				:placeholder="t('terms_of_service', 'By using this service …')"
				class="terms__textarea" />

			<p class="settings-hint">
				{{ t('terms_of_service', 'For formatting purposes Markdown is supported.') }}
			</p>

			<NcButton :disabled="saveButtonDisabled"
				@click="onSubmit">
				{{ saveButtonText }}
			</NcButton>
		</NcSettingsSection>

		<NcSettingsSection v-if="hasTerms"
			:name="t('terms_of_service', 'Existing terms of service')">
			<NcButton :disabled="resetButtonDisabled"
				type="error"
				@click="onResetSignatories">
				{{ resetButtonText }}
			</NcButton>

			<ul v-if="hasTerms">
				<Term v-for="term in terms"
					:key="term.id"
					v-bind="term" />
			</ul>
		</NcSettingsSection>
	</Fragment>
</template>

<script>
import { Fragment } from 'vue-frag'
import Term from './components/Term.vue'
import axios from '@nextcloud/axios'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateOcsUrl } from '@nextcloud/router'

// Styles
import '@nextcloud/dialogs/style.css'

export default {
	name: 'App',

	components: {
		Term,
		Fragment,
		NcButton,
		NcCheckboxRadioSwitch,
		NcSelect,
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
			showOnPublicShares: false,
			showForLoggedInUser: true,
		}
	},

	computed: {
		hasTerms() {
			return Object.keys(this.terms).length > 0
		},
	},

	watch: {
		showOnPublicShares(value) {
			if (!this.saveButtonDisabled) {
				OCP.AppConfig.setValue(
					'terms_of_service',
					'tos_on_public_shares',
					value ? '1' : '0',
				)
			}
		},
		showForLoggedInUser(value) {
			if (!this.saveButtonDisabled) {
				OCP.AppConfig.setValue(
					'terms_of_service',
					'tos_for_users',
					value ? '1' : '0',
				)
			}
		},
	},

	mounted() {
		this.saveButtonText = t('terms_of_service', 'Loading …')
		this.resetButtonText = t('terms_of_service', 'Reset all signatories')
		axios
			.get(generateOcsUrl('/apps/terms_of_service/terms/admin'))
			.then(response => {
				const data = response.data.ocs.data
				if (data.terms.length !== 0) {
					this.terms = data.terms
				}
				this.countries = data.countries
				this.languages = data.languages
				this.showOnPublicShares = data.tos_on_public_shares === '1'
				this.showForLoggedInUser = data.tos_for_users === '1'
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
				this.$nextTick(() => {
					this.saveButtonDisabled = false
				})
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
				.post(generateOcsUrl('/apps/terms_of_service/terms'),
					{
						countryCode: this.country.value,
						languageCode: this.language.value,
						body: this.body,
					})
				.then(response => {
					const data = response.data.ocs.data
					this.$set(this.terms, data.id, data)

					showSuccess(t('terms_of_service', 'Terms saved successfully!'))
					this.saveButtonDisabled = false
				})
		},
		onResetSignatories() {
			this.resetButtonDisabled = true

			axios
				.delete(generateOcsUrl('/apps/terms_of_service/sign'))
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

.form {
	margin-top: 30px;
	display: block;
}

.settings-hint {
	margin-top: 0;
}

label span {
	display: inline-block;
	min-width: 120px;
	padding: 8px 0;
	vertical-align: top;
}

</style>

<!--
  - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcSettingsSection
		:name="t('terms_of_service', 'Terms of service')"
		:description="t('terms_of_service', 'Require users to accept the terms of service before accessing the service.')">
		<NcCheckboxRadioSwitch
			v-model="showForLoggedInUser"
			type="switch">
			{{ t('terms_of_service', 'Show for logged-in users') }}
		</NcCheckboxRadioSwitch>

		<NcCheckboxRadioSwitch
			v-model="showOnPublicShares"
			type="switch">
			{{ t('terms_of_service', 'Show on public shares') }}
		</NcCheckboxRadioSwitch>
		<p class="edit-form">
			{{ t('terms_of_service', 'Enter or update terms of service below.') }}
		</p>
		<span class="form">
			<NcSelect
				v-model="country"
				:options="countryOptions"
				:placeholder="t('terms_of_service', 'Select a region')"
				:aria-label-combobox="t('terms_of_service', 'Select a region')"
				label="label"
				track-by="value" />
			<NcSelect
				v-model="language"
				:options="languageOptions"
				:placeholder="t('terms_of_service', 'Select a language')"
				:aria-label-combobox="t('terms_of_service', 'Select a language')"
				label="label"
				track-by="value" />
		</span>

		<textarea
			v-model="body"
			:placeholder="t('terms_of_service', 'By using this service …')"
			class="terms__textarea" />

		<p class="settings-hint">
			{{ t('terms_of_service', 'For formatting purposes Markdown is supported.') }}
		</p>
		<p class="terms-descr">
			{{ t('terms_of_service', 'Saving the terms will update the text but will not send a notification to users. Notifications are only sent if you reset the signatories.') }}
		</p>
		<NcButton
			:disabled="saveButtonDisabled"
			@click="onSubmit">
			{{ saveButtonText }}
		</NcButton>
	</NcSettingsSection>

	<NcSettingsSection
		v-if="hasTerms"
		:name="t('terms_of_service', 'Existing terms of service')">
		<p class="terms-descr">
			{{ t('terms_of_service', 'We recommend resetting the signatories if legal changes were applied. For minor changes like fixing typos or correcting links, it could be left out, as it would otherwise require all users to accept the Terms of Service again.') }}
		</p>
		<NcButton
			:disabled="resetButtonDisabled"
			variant="error"
			@click="onResetSignatories">
			{{ resetButtonText }}
		</NcButton>

		<ul v-if="hasTerms">
			<TermRow
				v-for="term in terms"
				:key="term.id"
				:countries="countries"
				:languages="languages"
				v-bind="term"
				@edited="onTermEdited"
				@deleted="onDeleted" />
		</ul>
	</NcSettingsSection>
</template>

<script>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { generateOcsUrl } from '@nextcloud/router'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import TermRow from './components/TermRow.vue'

// Styles
import '@nextcloud/dialogs/style.css'

export default {
	name: 'App',

	components: {
		TermRow,
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
				window.OCP.AppConfig.setValue(
					'terms_of_service',
					'tos_on_public_shares',
					value ? '1' : '0',
				)
			}
		},

		showForLoggedInUser(value) {
			if (!this.saveButtonDisabled) {
				window.OCP.AppConfig.setValue(
					'terms_of_service',
					'tos_for_users',
					value ? '1' : '0',
				)
			}
		},
	},

	mounted() {
		this.saveButtonText = t('terms_of_service', 'Loading …')
		this.resetButtonText = t('terms_of_service', 'Reset signatories & notify users')
		axios
			.get(generateOcsUrl('/apps/terms_of_service/terms/admin'))
			.then((response) => {
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

				this.saveButtonText = t('terms_of_service', 'Save terms')
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
				.post(
					generateOcsUrl('/apps/terms_of_service/terms'),
					{
						countryCode: this.country.value,
						languageCode: this.language.value,
						body: this.body,
					},
				)
				.then((response) => {
					const data = response.data.ocs.data
					this.terms[data.id] = data

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

		onTermEdited({ languageCode, countryCode, body }) {
			this.country = {
				value: countryCode,
				label: this.countries[countryCode] + ' (' + countryCode + ')',
			}

			this.language = {
				value: languageCode,
				label: this.languages[languageCode] + ' (' + languageCode + ')',
			}

			this.body = body
		},

		onDeleted(id) {
			delete this.terms[id]
		},

		t,
	},
}
</script>

<style lang="scss" scoped>
.terms__textarea {
	width: 100%;
	display: block;
}

.edit-form {
	margin-top: 30px;
	opacity: .7;
}

.form {
	margin-top: 10px;
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

.terms-descr {
	opacity: .7;
	margin-bottom: 10px;
}
</style>

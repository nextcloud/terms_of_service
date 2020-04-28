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
	<div id="terms_of_service" class="section">
		<h2>{{ t('terms_of_service', 'Terms of service') }}</h2>

		<p class="settings-hint">
			{{ t('terms_of_service', 'Require users to accept the terms of service before accessing the service.') }}
		</p>

		<p class="settings-hint">
			{{ t('terms_of_service', 'For formatting purposes Markdown is supported.') }}
		</p>

		<span>
			<Multiselect v-model="country"
				:options="countryOptions"
				:placeholder="t('terms_of_service', 'Select a region')"
				label="label"
				track-by="value" />
			<Multiselect v-model="language"
				:options="languageOptions"
				:placeholder="t('terms_of_service', 'Select a language')"
				label="label"
				track-by="value" />
		</span>

		<textarea id="terms_of_service-countryspecific-textarea" v-model="body" :placeholder="t('terms_of_service', 'By using this service …')" />
		<button :disabled="saveButtonDisabled" @click="onSubmit">
			{{ saveButtonText }}
		</button>

		<h3 v-if="hasTerms">
			{{ t('terms_of_service', 'Existing terms of service') }}
		</h3>

		<button :disabled="resetButtonDisabled" @click="onResetSignatories">
			{{ resetButtonText }}
		</button>

		<ul v-if="hasTerms" id="terms_of_service-countryspecific-list">
			<Term v-for="term in terms" :key="term.id" v-bind="term" />
		</ul>
	</div>
</template>

<script>
import Term from './components/Term'
import axios from '@nextcloud/axios'
import { Multiselect } from '@nextcloud/vue/dist/Components/Multiselect'

export default {
	name: 'App',

	components: {
		Term,
		Multiselect,
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
			.get(OC.generateUrl('/apps/terms_of_service/terms/admin'))
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
				OCP.Toast.error(t('terms_of_service', 'Ensure that all fields are filled'))
				return
			}

			this.saveButtonDisabled = true
			this.saveButtonText = t('terms_of_service', 'Saving …')

			axios
				.post(OC.generateUrl('/apps/terms_of_service/terms'),
					{
						countryCode: this.country.value,
						languageCode: this.language.value,
						body: this.body,
					})
				.then(response => {
					this.$set(this.terms, response.data.id, response.data)

					this.saveButtonText = t('terms_of_service', 'Saved!')
					setTimeout(() => {
						this.saveButtonText = t('terms_of_service', 'Save')
						this.saveButtonDisabled = false
					}, 2000)
				})
		},
		onResetSignatories() {
			this.resetButtonDisabled = true
			this.resetButtonText = t('terms_of_service', 'Resetting …')

			axios
				.delete(OC.generateUrl('/apps/terms_of_service/sign'))
				.then(() => {
					this.resetButtonText = t('terms_of_service', 'Reset!')
					setTimeout(() => {
						this.resetButtonText = t('terms_of_service', 'Reset all signatories')
						this.resetButtonDisabled = false
					}, 2000)
				})
		},
	},
}
</script>

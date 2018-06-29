<!--
 - @copyright Copyright (c) 2018 Joas Schilling <coding@schilljs.com>
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
	<div id="termsandconditions" class="section">
		<h2>{{ t('termsandconditions', 'Terms and conditions') }}</h2>

		<p class="settings-hint">{{ t('termsandconditions', 'Require users to accept the terms of service before accessing the service.') }}</p>

		<p class="settings-hint">{{ t('termsandconditions', 'For formatting purposes Markdown is supported.') }}</p>

		<span>
			<v-select v-model="country" :options="countryOptions" :placeholder="t('termsandconditions', 'Select a region')"></v-select>
			<v-select v-model="language" :options="languageOptions" :placeholder="t('termsandconditions', 'Select a language')"></v-select>
		</span>

		<textarea id="termsofservice-countryspecific-textarea" v-model="body" :placeholder="t('termsandconditions', 'By using this service …')"></textarea>
		<button @click="onSubmit" :disabled="saveButtonDisabled">{{saveButtonText}}</button>

		<h3 v-if="hasTerms">{{ t('termsandconditions', 'Existing terms and conditions') }}</h3>

		<button @click="onResetSignatories" :disabled="resetButtonDisabled">{{resetButtonText}}</button>

		<ul id="termsofservice-countryspecific-list" v-if="hasTerms">
			<term v-for="(t, key) in terms" v-bind="t" :key="t.id"></term>
		</ul>
	</div>
</template>

<script>
import term from './components/term';
import axios from 'axios';
import vSelect from 'vue-select';

export default {
	name: 'app',

	data () {
		return {
			country: null,
			language: null,
			body: '',
			countries: {},
			countryOptions: [],
			languages: {},
			languageOptions: [],
			terms: {},
			saveButtonText: '',
			saveButtonDisabled: true,
			resetButtonText: '',
			resetButtonDisabled: false
		}
	},

	methods: {
		onSubmit () {
			if (!this.country || !this.language || !this.body) {
				return;
			}

			this.saveButtonDisabled = true;
			this.saveButtonText = t('termsandconditions', 'Saving …');

			axios
				.post(OC.generateUrl('/apps/termsandconditions/terms'),
					{
						countryCode: this.country.value,
						languageCode: this.language.value,
						body: this.body
					},
					this.tokenHeaders)
				.then(response => {
					this.$set(this.terms, response.data.id, response.data);

					this.saveButtonText = t('termsandconditions', 'Saved!');
					setTimeout(() => {
						this.saveButtonText = t('termsandconditions', 'Save');
						this.saveButtonDisabled = false;
					}, 2000);
				});
		},
		onResetSignatories () {
			this.resetButtonDisabled = true;
			this.resetButtonText = t('termsandconditions', 'Resetting …');

			axios
				.delete(OC.generateUrl('/apps/termsandconditions/sign'), this.tokenHeaders)
				.then(response => {
					this.resetButtonText = t('termsandconditions', 'Reset!');
					setTimeout(() => {
						this.resetButtonText = t('termsandconditions', 'Reset all signatories');
						this.resetButtonDisabled = false;
					}, 2000);
				});
		}
	},

	components: {
		term,
		vSelect
	},

	computed: {
		hasTerms () {
			return Object.keys(this.terms).length > 0;
		},
		tokenHeaders () {
			return { headers: { requesttoken: OC.requestToken } };
		}
	},

	mounted () {
		this.saveButtonText = t('termsandconditions', 'Loading …');
		this.resetButtonText = t('termsandconditions', 'Reset all signatories');
		axios
			.get(OC.generateUrl('/apps/termsandconditions/terms'), this.tokenHeaders)
			.then(response => {
				this.terms = response.data.terms;
				this.countries = response.data.countryCodes;
				this.languages = response.data.languageCodes;
				Object.keys(this.countries).forEach((countryCode) => {
					this.countryOptions.push({
						value: countryCode,
						label: response.data.countryCodes[countryCode] + ' (' + countryCode + ')'
					});
				});
				Object.keys(this.languages).forEach((languageCode) => {
					this.languageOptions.push({
						value: languageCode,
						label: this.languages[languageCode] + ' (' + languageCode + ')'
					});
				});

				this.saveButtonText = t('termsandconditions', 'Save');
				this.saveButtonDisabled = false;
			});
	}
}
</script>

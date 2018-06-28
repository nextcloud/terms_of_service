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
			<select id="country-selector" v-model="country">
				<option value="">{{ t('termsandconditions', 'Select a region') }}</option>
				<option v-for="(value, key) in countries" :value="key">{{value}} ({{key}})</option>
			</select>

			<select id="language-selector" v-model="language">
				<option value="">{{ t('termsandconditions', 'Select a language') }}</option>
				<option v-for="(value, key) in languages" :value="key">{{value}} ({{key}})</option>
			</select>
		</span>

		<textarea id="termsofservice-countryspecific-textarea" v-model="body" :placeholder="t('termsandconditions', 'By using this service …')"></textarea>
		<button @click="onSubmit">{{ t('termsandconditions', 'Save') }}</button>

		<h3 v-if="hasTerms">{{ t('termsandconditions', 'Existing terms and conditions') }}</h3>

		<ul id="termsofservice-countryspecific-list" v-if="hasTerms">
			<term v-for="(t, key) in terms" v-bind="t" :key="t.id"></term>
		</ul>
	</div>
</template>

<script>
import term from './components/term';
import axios from 'axios';

export default {
	name: 'app',

	data () {
		return {
			country: '',
			language: '',
			body: '',
			countries: [],
			languages: [],
			terms: {}
		}
	},

	methods: {
		onSubmit () {
			if (!this.country || !this.language || !this.body) {
				return;
			}

			axios
				.post(OC.generateUrl('/apps/termsandconditions/terms'),
					{
						countryCode: this.country,
						languageCode: this.language,
						body: this.body
					},
					this.tokenHeaders)
				.then(response => {
					this.$set(this.terms, response.data.id, response.data);
				});
		}
	},

	components: {
		term
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
		axios
			.get(OC.generateUrl('/apps/termsandconditions/terms'), this.tokenHeaders)
			.then(response => {
				this.terms = response.data.terms;
				this.countries = response.data.countryCodes;
				this.languages = response.data.languageCodes;
			});
	}
}
</script>

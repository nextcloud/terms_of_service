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
	<div id="terms_of_service_confirm">
		<modal name="confirm-terms" @before-close="beforeClose">
			<div id="tos-overlay">
				<h3>{{ t('terms_of_service', 'Terms of service') }}</h3>
				<select v-if="terms.length > 1" v-model="selectedLanguage">
					<option v-for="(language, index) in languages" :value="index">{{language}}</option>
				</select>
				<div class="clear-both" />

				<div class="text-content" v-html="termsBody"></div>
				<button class="primary" @click="acceptTerms">{{ t('terms_of_service', 'I acknowledge that I have read and agree to the above terms of service') }}</button>
			</div>
		</modal>
	</div>
</template>

<script>
	import axios from 'nextcloud-axios';

export default {
	name: 'userapp',

	data () {
		return {
			hasSigned: false,
			terms: {},
			languages: [],
			selectedLanguage: 0,
			termsId: 0,
			termsBody: ''
		}
	},

	mounted () {
		this.loadTerms();
	},

	watch: {
		selectedLanguage: function(newLanguage) {
			this.selectTerms(newLanguage);
		}
	},

	methods: {
		loadTerms () {
			axios
				.get(OC.generateUrl('/apps/terms_of_service/terms'))
				.then(response => {
					this.hasSigned = response.data.hasSigned;
					this.terms = response.data.terms;

					const language = OC.getLanguage().split('-')[0];

					if (!this.terms.length || this.hasSigned) {
						return;
					}

					this.selectTerms(0);
					if (this.terms.length > 1) {
						Object.keys(this.terms).forEach((index) => {
							if (language === this.terms[index].languageCode) {
								this.selectedLanguage = index;
							}

							this.languages.push(response.data.languages[this.terms[index].languageCode]);
						});
					}

					this.showTerms();
				});
		},
		selectTerms (index) {
			this.termsBody = this.terms[index].renderedBody;
			this.termsId = this.terms[index].id;
		},
		showTerms () {
			this.$modal.show('confirm-terms');
		},
		acceptTerms () {
			this.hasSigned = true;
			this.$modal.hide('confirm-terms');

			axios.post(
				OC.generateUrl('/apps/terms_of_service/sign'),
				{
					termId: this.termsId
				}
			).then(() => {
				window.location.reload();
			});
		},
		beforeClose (event) {
			if (!this.hasSigned) {
				event.stop();
			}
		}
	}
}
</script>

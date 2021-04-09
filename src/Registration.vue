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
	<div id="terms_of_service">
		<p>
			<input id="terms_of_service_accepted"
				v-model="hasSigned"
				:value="termsId"
				type="checkbox"
				name="terms_of_service_accepted"
				class="checkbox">
			<label
				for="terms_of_service_accepted"
				@click.prevent.stop="showTerms">
				{{ t('registration', 'I acknowledge that I have read and agree to the above terms of service') }}
			</label>
		</p>

		<div id="terms_of_service_confirm">
			<modal name="confirm-terms"
				:adaptive="true"
				@before-close="beforeClose">
				<div id="tos-overlay">
					<h3>{{ t('terms_of_service', 'Terms of service') }}</h3>
					<select v-if="terms.length > 1" v-model="selectedLanguage">
						<option v-for="(language, index) in languages" :key="index" :value="index">
							{{ language }}
						</option>
					</select>
					<div class="clear-both" />

					<div class="text-content" v-html="termsBody" />

					<button class="primary"
						@click.prevent.stop="acceptTerms">
						{{ t('terms_of_service', 'I acknowledge that I have read and agree to the above terms of service') }}
					</button>
				</div>
			</modal>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import '@nextcloud/dialogs/styles/toast.scss'

export default {
	name: 'Registration',

	data() {
		return {
			hasSigned: false,
			terms: {},
			languages: [],
			selectedLanguage: 0,
			termsId: 0,
			termsBody: '',
			publicContent: null,
		}
	},

	watch: {
		selectedLanguage(newLanguage) {
			this.selectTerms(newLanguage)
		},
	},

	mounted() {
		this.loadTerms()
	},

	methods: {
		loadTerms() {
			axios
				.get(generateUrl('/apps/terms_of_service/terms'))
				.then(response => {
					// this.hasSigned = response.data.hasSigned
					this.terms = response.data.terms

					const language = OC.getLanguage().split('-')[0]

					if (!this.terms.length || this.hasSigned) {
						return
					}

					// make it Vue
					this.publicContent = document.getElementById('files-public-content')
					if (this.publicContent !== null) {
						this.publicContent.style.visibility = 'hidden'
					}

					this.selectTerms(0)
					if (this.terms.length > 1) {
						Object.keys(this.terms).forEach((index) => {
							if (language === this.terms[index].languageCode) {
								this.selectedLanguage = index
							}

							this.languages.push(response.data.languages[this.terms[index].languageCode])
						})
					}

					this.showTerms()
				})
		},
		selectTerms(index) {
			this.termsBody = this.terms[index].renderedBody
			this.termsId = this.terms[index].id
		},
		showTerms() {
			this.$modal.show('confirm-terms')
		},
		acceptTerms() {
			this.hasSigned = true
			this.$modal.hide('confirm-terms')
		},
		beforeClose(event) {
			if (!this.hasSigned) {
				event.stop()
			}
		},
	},
}
</script>

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
		<Modal v-if="showModal"
			:can-close="hasSigned"
			@close="handleCloseModal">
			<ModalContent @click="acceptTerms">
				<template #header>
					<h3>{{ t('terms_of_service', 'Terms of service') }}</h3>
					<select v-if="terms.length > 1" v-model="selectedLanguage">
						<option v-for="(language, index) in languages" :key="index" :value="index">
							{{ language }}
						</option>
					</select>
				</template>

				<!-- eslint-disable-next-line vue/no-v-html -->
				<div class="text-content" v-html="termsBody" />
			</ModalContent>
		</Modal>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Modal from '@nextcloud/vue/dist/Components/Modal'
import ModalContent from './components/ModalContent.vue'

export default {
	name: 'UserApp',

	components: {
		Modal,
		ModalContent,
	},

	data() {
		return {
			showModal: false,
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
					this.hasSigned = response.data.hasSigned
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
			this.showModal = true
		},

		acceptTerms() {
			this.hasSigned = true
			this.showModal = false

			let url = '/apps/terms_of_service/sign'
			if (this.$root.source === 'public') {
				url = '/apps/terms_of_service/sign_public'
			}

			axios.post(
				generateUrl(url),
				{
					termId: this.termsId,
				}
			).then(() => {
				window.location.reload()
			})
		},

		handleCloseModal() {
			this.showModal = false
		},
	},
}
</script>

<style lang="scss" scoped>

::v-deep .modal-container {
	display: flex;
	height: 100%;
}
</style>

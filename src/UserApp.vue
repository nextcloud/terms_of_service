<!--
  - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div id="terms_of_service_confirm">
		<NcModal v-if="showModal"
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
		</NcModal>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import ModalContent from './components/ModalContent.vue'

export default {
	name: 'UserApp',

	components: {
		NcModal,
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
				{ termId: this.termsId },
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

:deep .modal-container {
	display: flex;
	height: 100%;
}
</style>

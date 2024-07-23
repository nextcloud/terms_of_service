<!--
  - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div id="terms_of_service">
		<p v-if="terms.length">
			<input id="terms_of_service_accepted"
				v-model="hasSigned"
				:value="termsId"
				type="checkbox"
				name="terms_of_service_accepted"
				class="checkbox"
				@keydown.enter.prevent.stop="showTerms">
			<label for="terms_of_service_accepted"
				@click.prevent.stop="showTerms"
				@keydown.enter.prevent.stop="showTerms">
				{{ t('terms_of_service', 'I acknowledge that I have read and agree to the above terms of service') }} ↗
			</label>
		</p>

		<div id="terms_of_service_confirm">
			<NcModal v-if="showModal"
				:can-close="hasSigned"
				@close="handleCloseModal">
				<ModalContent :is-scroll-complete="hasScrolledToBottom" @click="acceptTerms">
					<template #header>
						<h3>{{ t('terms_of_service', 'Terms of service') }}</h3>
						<select v-if="terms.length > 1" v-model="selectedLanguage">
							<option v-for="(language, index) in languages" :key="index" :value="index">
								{{ language }}
							</option>
						</select>
					</template>

					<!-- eslint-disable-next-line vue/no-v-html -->
					<div ref="termsContent"
						class="text-content"
						@scroll="checkScroll"
						v-html="termsBody" />
				</ModalContent>
			</NcModal>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import ModalContent from './components/ModalContent.vue'

// Styles
import '@nextcloud/dialogs/style.css'
export default {
	name: 'Registration',

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
			hasScrolledToBottom: false,
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
		async loadTerms() {
			try {
				const response = await axios.get(generateUrl('/apps/terms_of_service/terms'))

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
				// this.showTerms()
			} catch (e) {
				console.error(e)
			}
		},

		selectTerms(index) {
			this.termsBody = this.terms[index].renderedBody
			this.termsId = this.terms[index].id
		},

		showTerms() {
			if (this.hasSigned) {
				this.hasSigned = false
				return
			}
			this.showModal = true
			this.$nextTick(() => {
				this.checkScroll()
			})
		},
		checkScroll() {
			const termsContent = this.$refs.termsContent
			const isScrollable = termsContent.scrollHeight > termsContent.clientHeight
			this.hasScrolledToBottom = !isScrollable || (termsContent.scrollHeight - termsContent.scrollTop <= termsContent.clientHeight + 1)
		},

		acceptTerms() {
			this.hasSigned = true
			this.showModal = false
		},

		handleCloseModal() {
			this.showModal = false
		},
	},
}
</script>
<style scoped lang="scss">
:deep .modal-container {
	display: flex;
	height: 100%;
}
</style>

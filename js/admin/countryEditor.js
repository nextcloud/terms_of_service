/**
 * @copyright Copyright (c) 2017 Lukas Reschke <lukas@statuscode.ch>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function(OCA) {
	OCA.TermsAndConditions = OCA.TermsAndConditions || {};
	OCA.TermsAndConditions.AdminCountryEditor = {
		/**
		 * @param {int} id
		 */
		loadById: function(id) {
			var element = cachedTermsAndConditions.terms[id];
			$('#termsofservice-countryspecific-textarea').val(element.body);
			$('#country-selector').val(element.countryCode).trigger('change');
			$('#language-selector').val(element.languageCode).trigger('change');
		},

		saveTerms: function() {
			$.ajax({
				url: OC.generateUrl('/apps/termsandconditions/terms'),
				type: 'POST',
				data: {
					countryCode: $('#country-selector').val(),
					languageCode: $('#language-selector').val(),
					body: $('#termsofservice-countryspecific-textarea').val()
				},
				success: function() {
					OC.msg.finishedSaving('#terms_of_service_settings_msg', {status: 'success', data: {message: t('termsandconditions', 'Saved')}});
					$('#terms_of_service_settings_loading').hide();
					var loading = OCA.TermsAndConditions.AdminLoading;
					loading.load();
				}
			});
		},

		/**
		 * @param {int} id
		 */
		deleteById: function(id) {
			$.ajax({
				url: OC.generateUrl('/apps/termsandconditions/terms/' + id),
				type: 'DELETE',
				success: function() {
					OC.msg.finishedSaving('#terms_of_service_settings_msg', {status: 'success', data: {message: t('termsandconditions', 'Saved')}});
					$('#terms_of_service_settings_loading').hide();
					var loading = OCA.TermsAndConditions.AdminLoading;
					loading.load();
				}
			});
		}
	};
})(OCA);

(function () {
	$('#termsofservice-countryspecific-save').click(function() {
		var countryEditor = OCA.TermsAndConditions.AdminCountryEditor;
		countryEditor.saveTerms();
	});
})();

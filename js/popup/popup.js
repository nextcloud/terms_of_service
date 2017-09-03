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
	OCA.TermsAndConditions.Popup = {
		mappings: [],
		serverResponse: [],
		signingType: null,
		signingId: null,

		OVERLAY_TEMPLATE: '' +
		'<div id="tos-overlay" class="hidden" style="background-color: white; border-radius: 4px; width: 90%">' +
		'	<div style="padding: 20px;">' +
		'		<h3 style="float: left;">' + t('termsandconditions', 'Terms and Conditions') + '</h3>' +
		'		<select style="float:right;" id="tos-language-chooser"></select>' +
		'		<select style="float:right;" id="tos-country-chooser"></select>' +
		'		<div style="clear: both;"></div>' +
		'		<input type="hidden" id="tos-current-selected-id"/>' +
		'		<span class="text-content"></span>' +
		'		<img style="display: block; margin-left: auto; margin-right: auto;" class="float-spinner" alt="" src="' + OC.imagePath('core', 'loading-dark.gif') + '"/>' +
		'		<div style="clear: both;"></div>' +
		'		<button style="width: 100%">' + t('termsandconditions', 'I acknowledge that I have read and agree to the above Terms and Conditions') + '</button>' +
		'	</div>' +
		'</div>',

		initialize: function () {
			$('body').prepend(OCA.TermsAndConditions.Popup.OVERLAY_TEMPLATE);
			$('#tos-country-chooser').on('change', function () {
				OCA.TermsAndConditions.Popup.selectCountry(this.value);
			});

			$('#tos-language-chooser').on('change', function () {
				OCA.TermsAndConditions.Popup.loadTermsOfServiceText($('#tos-country-chooser').val(), this.value);
			});

			$('#tos-overlay > div > button').on('click', function() {
				OCA.TermsAndConditions.Popup.sign();
			});
		},

		/**
		 * @param {string} countryCode
		 * @param {string} languageCode
		 */
		loadTermsOfServiceText: function(countryCode, languageCode) {
			var selectedTerms = OCA.TermsAndConditions.Popup.mappings[countryCode][languageCode];
			$('#tos-overlay > div > span').html(selectedTerms.renderedBody);
			$('#tos-current-selected-id').val(selectedTerms.id);
		},

		/**
		 * @param {string} countryCode
		 */
		selectCountry: function(countryCode) {
			// Clear the existing language codes
			$('#tos-language-chooser').html('');

			// Create the language codes
			Object.keys(OCA.TermsAndConditions.Popup.mappings[countryCode]).forEach(function(key) {
				$('#tos-language-chooser')
					.append($('<option></option>')
						.attr('value', key)
						.text(OCA.TermsAndConditions.Popup.serverResponse.languageCodes[key]));
			});

			// Select the best matching language
			var bestLanguage = OCA.TermsAndConditions.Popup.serverResponse.currentSession.languageCode;
			if($("#tos-language-chooser option[value='"+bestLanguage+"']").length > 0) {
				$("#tos-language-chooser option[value='"+bestLanguage+"']").attr('selected','selected');
			}
			$("#tos-country-chooser option[value='"+countryCode+"']").attr('selected','selected');

			OCA.TermsAndConditions.Popup.loadTermsOfServiceText(countryCode, $("#tos-language-chooser").val());
			document.cookie = 'TermsAndConditionsCountryCookie='+countryCode+'; path=/';
		},

		loadTerms: function() {
			$.get(OC.generateUrl('/apps/termsandconditions/terms')).done(function (response) {
				OCA.TermsAndConditions.Popup.serverResponse = response;
				var hasTerms = false;
				$.each(response.terms, function(id, terms) {
					hasTerms = true;
					if (typeof OCA.TermsAndConditions.Popup.mappings[terms.countryCode] === "undefined") {
						OCA.TermsAndConditions.Popup.mappings[terms.countryCode] = [];
					}
					OCA.TermsAndConditions.Popup.mappings[terms.countryCode][terms.languageCode] = terms;
				});

				if(hasTerms === false) {
					return;
				}

				// Create the country divs
				Object.keys(OCA.TermsAndConditions.Popup.mappings).forEach(function(key) {
					$('#tos-country-chooser')
						.append($('<option></option>')
							.attr('value', key)
							.text(response.countryCodes[key]));
				});

				// Select the best matching country
				var defaultCountry = response.currentSession.countryCode;
				if(typeof OCA.TermsAndConditions.Popup.mappings[defaultCountry] === 'undefined') {
					if(typeof OCA.TermsAndConditions.Popup.mappings['--'] === 'undefined') {
						OCA.TermsAndConditions.Popup.selectCountry($('#tos-country-chooser').val());
					} else {
						OCA.TermsAndConditions.Popup.selectCountry('--');
					}
				} else {
					OCA.TermsAndConditions.Popup.selectCountry(defaultCountry);
				}
				$('#tos-overlay > div > .float-spinner').css('display', 'none');

				var interceptor = OCA.TermsAndConditions.Interceptor;
				interceptor.initialize();
			});
		},

		/**
		 * @param {int} type
		 * @param {string} id
		 */
		show : function(type, id) {
			OCA.TermsAndConditions.Popup.signingId = id;
			OCA.TermsAndConditions.Popup.signingType = type;
			$('#tos-overlay').popup({autoopen: true, escape: false, blur: false});
		},

		sign : function() {
			OCA.TermsAndConditions.Signer.sign(
				OCA.TermsAndConditions.Popup.signingType,
				OCA.TermsAndConditions.Popup.signingId
			);
		},

		hide : function() {
			$('#tos-overlay').popup('hide');
			window.location.reload();
		}
	}
})(OCA);

$(document).ready(function() {
	var popup = OCA.TermsAndConditions.Popup;
	popup.initialize();
	popup.loadTerms();
});

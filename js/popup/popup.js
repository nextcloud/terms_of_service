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
	OCA.TermsOfService = OCA.TermsOfService || {};
	OCA.TermsOfService.Popup = {
		mappings: [],
		serverResponse: [],
		signingType: null,
		signingId: null,

		acceptButtonLanguages : {
			'DE': 'Ich bestätige, dass ich die obigen Allgemeinen Geschäftsbedingungen gelesen habe und akzeptiere.',
			'DEFAULT':  t('terms_of_service', 'I acknowledge that I have read and agree to the above terms of service'),
		},

		OVERLAY_TEMPLATE: '' +
		'<div id="tos-overlay" class="hidden" style="background-color: white; border-radius: 4px; width: 90%">' +
		'	<div style="padding: 20px;">' +
		'		<h3 style="float: left;">' + t('terms_of_service', 'Terms of service') + '</h3>' +
		'		<select style="float:right;" id="tos-language-chooser"></select>' +
		'		<select style="float:right;" id="tos-country-chooser"></select>' +
		'		<div style="clear: both;"></div>' +
		'		<input type="hidden" id="tos-current-selected-id"/>' +
		'		<span class="text-content"></span>' +
		'		<img style="display: block; margin-left: auto; margin-right: auto;" class="float-spinner" alt="" src="' + OC.imagePath('core', 'loading-dark.gif') + '"/>' +
		'		<div style="clear: both;"></div>' +
		'		<button id="tos-accept-button" style="width: 100%">' + t('terms_of_service', 'I acknowledge that I have read and agree to the above terms of service') + '</button>' +
		'	</div>' +
		'</div>',

		initialize: function () {
			$('body').prepend(OCA.TermsOfService.Popup.OVERLAY_TEMPLATE);
			$('#tos-country-chooser').on('change', function () {
				OCA.TermsOfService.Popup.selectCountry(this.value);
			});

			$('#tos-language-chooser').on('change', function () {
				OCA.TermsOfService.Popup.loadTermsOfServiceText($('#tos-country-chooser').val(), this.value);
			});

			$('#tos-overlay > div > button').on('click', function() {
				OCA.TermsOfService.Popup.sign();
			});
		},

		/**
		 * @param {string} countryCode
		 * @param {string} languageCode
		 */
		loadTermsOfServiceText: function(countryCode, languageCode) {
			var selectedTerms = OCA.TermsOfService.Popup.mappings[countryCode][languageCode];
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
			Object.keys(OCA.TermsOfService.Popup.mappings[countryCode]).forEach(function(key) {
				$('#tos-language-chooser')
					.append($('<option></option>')
						.attr('value', key)
						.text(OCA.TermsOfService.Popup.serverResponse.languageCodes[key]));
			});

			// Select the best matching language
			var bestLanguage = OCA.TermsOfService.Popup.serverResponse.currentSession.languageCode;
			if($("#tos-language-chooser option[value='"+bestLanguage+"']").length > 0) {
				$("#tos-language-chooser option[value='"+bestLanguage+"']").attr('selected','selected');
			}

			if($("#tos-language-chooser").val() === 'de') {
				$('#tos-accept-button').html(OCA.TermsOfService.Popup.acceptButtonLanguages.DE);
			} else {
				$('#tos-accept-button').html(OCA.TermsOfService.Popup.acceptButtonLanguages.DEFAULT);
			}
			$("#tos-country-chooser option[value='"+countryCode+"']").attr('selected','selected');

			OCA.TermsOfService.Popup.loadTermsOfServiceText(countryCode, $("#tos-language-chooser").val());
			document.cookie = 'TermsOfServiceCountryCookie='+countryCode+'; path=/';
		},

		loadTerms: function() {
			$.get(OC.generateUrl('/apps/terms_of_service/terms')).done(function (response) {
				OCA.TermsOfService.Popup.serverResponse = response;
				var hasTerms = false;
				$.each(response.terms, function(id, terms) {
					hasTerms = true;
					if (typeof OCA.TermsOfService.Popup.mappings[terms.countryCode] === "undefined") {
						OCA.TermsOfService.Popup.mappings[terms.countryCode] = [];
					}
					OCA.TermsOfService.Popup.mappings[terms.countryCode][terms.languageCode] = terms;
				});

				if(hasTerms === false) {
					return;
				}

				// Create the country divs
				Object.keys(OCA.TermsOfService.Popup.mappings).forEach(function(key) {
					$('#tos-country-chooser')
						.append($('<option></option>')
							.attr('value', key)
							.text(response.countryCodes[key]));
				});

				// Select the best matching country
				var defaultCountry = response.currentSession.countryCode;
				if(typeof OCA.TermsOfService.Popup.mappings[defaultCountry] === 'undefined') {
					if(typeof OCA.TermsOfService.Popup.mappings['--'] === 'undefined') {
						OCA.TermsOfService.Popup.selectCountry($('#tos-country-chooser').val());
					} else {
						OCA.TermsOfService.Popup.selectCountry('--');
					}
				} else {
					OCA.TermsOfService.Popup.selectCountry(defaultCountry);
				}
				$('#tos-overlay > div > .float-spinner').css('display', 'none');

				var interceptor = OCA.TermsOfService.Interceptor;
				interceptor.initialize();
			});
		},

		/**
		 * @param {int} type
		 * @param {string} id
		 */
		show : function(type, id) {
			OCA.TermsOfService.Popup.signingId = id;
			OCA.TermsOfService.Popup.signingType = type;
			$('#tos-overlay').popup({autoopen: true, escape: false, blur: false});
		},

		sign : function() {
			OCA.TermsOfService.Signer.sign(
				OCA.TermsOfService.Popup.signingType,
				OCA.TermsOfService.Popup.signingId
			);
		},

		hide : function() {
			$('#tos-overlay').popup('hide');
			window.location.reload();
		}
	}
})(OCA);

$(document).ready(function() {
	var popup = OCA.TermsOfService.Popup;
	popup.initialize();
	popup.loadTerms();
});

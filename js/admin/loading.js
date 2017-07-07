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

var cachedTermsAndConditions = [];

(function(OCA) {
	OCA.TermsAndConditions = OCA.TermsAndConditions || {};
	OCA.TermsAndConditions.AdminLoading = {
		load: function() {
			$('#termsofservice-countryspecific-list').html('');
			$.get(OC.generateUrl('/apps/termsandconditions/terms')).done(function (response) {
				cachedTermsAndConditions = response;
				$.each(response.terms, function (key, terms) {
					$('#termsofservice-countryspecific-list').append('<li>'+escapeHTML(response.countryCodes[terms.countryCode])+' ('+escapeHTML(response.languageCodes[terms.languageCode])+') <button class="termsandconditions-edit-country" data-id="'+terms.id+'">'+t('termsandconditions', 'Edit')+'</button><button class="termsandconditions-delete-country" data-id="'+terms.id+'">'+t('termsandconditions', 'Delete')+'</button></li>');
				});

				$('.termsandconditions-edit-country').click(function() {
					var countryEditor = OCA.TermsAndConditions.AdminCountryEditor;
					countryEditor.loadById($(this).data('id'));
				});

				$('.termsandconditions-delete-country').click(function() {
					var countryEditor = OCA.TermsAndConditions.AdminCountryEditor;
					countryEditor.deleteById($(this).data('id'));
				});
			})
		}
	};
})(OCA);

$(document).ready(function() {
	var loader = OCA.TermsAndConditions.AdminLoading;
	loader.load();
});

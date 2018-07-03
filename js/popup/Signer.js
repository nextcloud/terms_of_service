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
	OCA.TermsOfService.Signer = {

		signLogin : function() {
			$.ajax({
				url: OC.generateUrl('/apps/terms_of_service/sign/login'),
				type: 'POST',
				data: {
					termId: $('#tos-current-selected-id').val()
				},
				success: function() {
					OCA.TermsOfService.Popup.hide();
				}
			});
		},

		/**
		 * Taken from https://stackoverflow.com/a/15724300
		 * @param {string} name
		 */
		getCookie: function(name) {
			var value = "; " + document.cookie;
			var parts = value.split("; " + name + "=");
			if (parts.length == 2) return parts.pop().split(";").shift();
		},

		/**
		 * @param {string} shareId
		 */
		signInternalShare : function(shareId) {
			$.ajax({
				url: OC.generateUrl('/apps/terms_of_service/sign/internalShare'),
				type: 'POST',
				data: {
					termId: $('#tos-current-selected-id').val(),
					shareId: shareId,
				},
				success: function() {
					OCA.TermsOfService.Popup.hide();
				}
			});
		},

		/**
		 * @param {string} publicShareId
		 */
		signPublicShare : function(publicShareId) {
			$.ajax({
				url: OC.generateUrl('/apps/terms_of_service/sign/publicShare'),
				type: 'POST',
				data: {
					termId: $('#tos-current-selected-id').val(),
					publicShareId: publicShareId,
				},
				success: function() {
					var cookieName = 'TermsOfServiceShareIdCookie';
					var cookieValue = [];

					var existingCookieValue = OCA.TermsOfService.Signer.getCookie(cookieName);
					if(typeof existingCookieValue === "undefined") {
						cookieValue = [publicShareId];
					} else {
						cookieValue = JSON.parse(existingCookieValue);
						cookieValue.push(publicShareId);
					}

					document.cookie = cookieName+'='+JSON.stringify(cookieValue)+'; path=/';

					OCA.TermsOfService.Popup.hide();
				}
			});
		},

		/**
		 * @param {id} type
		 * @param {string} id
		 */
		sign : function(type, id) {
			switch(type) {
				case OCA.TermsOfService.AccessTypes.Login:
					OCA.TermsOfService.Signer.signLogin();
					break;
				case OCA.TermsOfService.AccessTypes.PUBLIC_SHARE:
					OCA.TermsOfService.Signer.signPublicShare(id);
					break;
				case OCA.TermsOfService.AccessTypes.INTERNAL_SHARE:
					OCA.TermsOfService.Signer.signInternalShare(id);
					break;
				default:
					break;
			}
		}
	}
})(OCA);

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
	OCA.TermsOfService.Interceptor = {

		initialize: function() {
			OCA.TermsOfService.Interceptor.checkLogin();
			OCA.TermsOfService.Interceptor.accessSharedStorage();
			OCA.TermsOfService.Interceptor.accessPublicShares();
		},

		checkLogin : function() {
			if(oc_current_user !== null) {
				if(!OCA.TermsOfService.Popup.serverResponse.signatories.hasSignedLogin) {
					OCA.TermsOfService.Popup.show();
				}
			}
		},

		accessSharedStorage: function() {
			$(document).on('click', '#fileList > tr', function(e) {
				var data = $(this).data();

				if(data.mounttype === 'shared-root') {
					if($.inArray(data.id, OCA.TermsOfService.Popup.serverResponse.signatories.signedStorages) === -1) {
						OCA.TermsOfService.Popup.show(OCA.TermsOfService.AccessTypes.INTERNAL_SHARE, data.id);
						e.preventDefault();
					}
				}
			});

			var previousFileId = FileList.dirInfo.id;

			// FIXME: Don't run on subfolders
			function checkSharedFilePermissions() {
				if(isNaN(previousFileId)) {
					previousFileId = FileList.dirInfo.id;
					setTimeout(checkSharedFilePermissions, 50);
					return;
				}

				if(FileList.dirInfo.mountType === 'shared' && $.inArray(FileList.dirInfo.id, OCA.TermsOfService.Popup.serverResponse.signatories.signedStorages) === -1) {
						OCA.TermsOfService.Popup.show(OCA.TermsOfService.AccessTypes.INTERNAL_SHARE, FileList.dirInfo.id);
				}
			}

			checkSharedFilePermissions();
		},

		accessPublicShares: function() {
			var currentToken = $('#sharingToken').val();
			if(typeof currentToken !== "undefined") {
				if($.inArray(currentToken, OCA.TermsOfService.Popup.serverResponse.signatories.signedPublicLinks) === -1) {
					OCA.TermsOfService.Popup.show(OCA.TermsOfService.AccessTypes.PUBLIC_SHARE, currentToken);
				}
			}
		}
	}
})(OCA);

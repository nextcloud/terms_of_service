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
	OCA.TermsAndConditions.Interceptor = {

		initialize: function() {
			OCA.TermsAndConditions.Interceptor.checkLogin();
			OCA.TermsAndConditions.Interceptor.accessSharedStorage();
			OCA.TermsAndConditions.Interceptor.accessPublicShares();
		},

		checkLogin : function() {
			if(oc_current_user !== null) {
				if(!OCA.TermsAndConditions.Popup.serverResponse.signatories.hasSignedLogin) {
					OCA.TermsAndConditions.Popup.show();
				}
			}
		},

		accessSharedStorage: function() {
			$(document).on('click', '#fileList > tr', function(e) {
				var data = $(this).data();

				if(data.mounttype === 'shared-root') {
					if($.inArray(data.id, OCA.TermsAndConditions.Popup.serverResponse.signatories.signedStorages) === -1) {
						OCA.TermsAndConditions.Popup.show(OCA.TermsAndConditions.AccessTypes.INTERNAL_SHARE, data.id);
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

				if(FileList.dirInfo.mountType === 'shared' && $.inArray(FileList.dirInfo.id, OCA.TermsAndConditions.Popup.serverResponse.signatories.signedStorages) === -1) {
						OCA.TermsAndConditions.Popup.show(OCA.TermsAndConditions.AccessTypes.INTERNAL_SHARE, FileList.dirInfo.id);
				}
			}

			checkSharedFilePermissions();
		},

		accessPublicShares: function() {
			var currentToken = $('#sharingToken').val();
			if(typeof currentToken !== "undefined") {
				if($.inArray(currentToken, OCA.TermsAndConditions.Popup.serverResponse.signatories.signedPublicLinks) === -1) {
					OCA.TermsAndConditions.Popup.show(OCA.TermsAndConditions.AccessTypes.PUBLIC_SHARE, currentToken);
				}
			}
		}
	}
})(OCA);

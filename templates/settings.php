<?php
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
script('termsandconditions', 'admin/merged');
style('termsandconditions', 'admin');

/** @var \OCP\IL10N $l */
/** @var array $_ */
/** @var array $countries */
$countries = $_['countries'];
/** @var array $languages */
$languages = $_['languages'];
?>

<div id="termsofservice" class="section">
	<h2><?php p($l->t('Terms and conditions')) ?></h2>
	<p class="settings-hint"><?php p($l->t('Require users to accept the terms of service before accessing the service.')); ?></p>
	<div id="terms_of_service_settings_status">
		<div id="terms_of_service_settings_loading" class="icon-loading-small" style="display: none;"></div>
		<span id="terms_of_service_settings_msg" class="msg success" style="display: none;"><?php p($l->t('Saved')) ?></span>
	</div>

	<p>
		<input type="checkbox" id="requireForAccessingInternalShares" class="checkbox" value="1" checked>
		<label for="requireForAccessingInternalShares"><?php p($l->t('Require accepting terms of service before accessing new internal shares')) ?></label><br>
	</p>
	<p>
		<input type="checkbox" id="requireForAccessingLinkShares" class="checkbox" value="1" checked>
		<label for="requireForAccessingLinkShares"><?php p($l->t('Require accepting terms of service before accessing public link shares')) ?></label><br>
	</p>

	<h3><?php p($l->t('Existing terms and conditions')) ?></h3>
	<p class="settings-hint"><?php p($l->t('For formatting purposes Markdown is supported.')); ?></p>

	<span>
		<select id="country-selector">
			<?php foreach($countries as $code => $countryName): ?>
				<option value="<?php p($code) ?>"><?php p($countryName . ' (' . $code . ')') ?></option>
			<?php endforeach; ?>
		</select>
		<select id="language-selector">
			<?php foreach($languages as $code => $languageName): ?>
				<option value="<?php p($code) ?>"><?php p($languageName . ' (' . $code . ')') ?></option>
			<?php endforeach; ?>
		</select>
	</span>

	<span>
		<textarea id="termsofservice-countryspecific-textarea" placeholder="<?php p('By using this service…') ?>"></textarea>
		<button id="termsofservice-countryspecific-save"><?php p($l->t('Save')) ?></button>
	</span>

	<span>
		<ul id="termsofservice-countryspecific-list"></ul>
	</span>
</div>

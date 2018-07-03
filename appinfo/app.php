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

require_once __DIR__ . '/../vendor/autoload.php';

\OCP\Util::addStyle('terms_of_service', 'overlay');
\OCP\Util::addScript('terms_of_service', 'popup/merged');

$app = new \OCA\TermsOfService\AppInfo\Application(
	\OC::$server->getRequest(),
	\OC::$server->getUserSession(),
	\OC::$server->query(\OCA\TermsOfService\Db\Mapper\SignatoryMapper::class),
	\OC::$server->query(\OCA\TermsOfService\Db\Mapper\TermsMapper::class),
	\OC::$server->query(\OCA\TermsOfService\CountryDetector::class)
);
\OCP\Util::connectHook('OC_Filesystem', 'preSetup', $app, 'addStorageWrapper');


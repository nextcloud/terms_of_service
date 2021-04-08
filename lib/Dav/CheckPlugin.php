<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Julien Veyssier <eneiluj@posteo.net>
 *
 * @author Julien Veyssier (eneiluj) <eneiluj@posteo.net>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\TermsOfService\Dav;

use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\DAV\Exception\Forbidden;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

use OCA\TermsOfService\AppInfo\Application;
use OCA\TermsOfService\Checker;

class CheckPlugin extends ServerPlugin {
	/** @var Server */
	protected $server;

	/**
	 * Initializes the plugin and registers event handlers
	 *
	 * @param Server $server
	 * @return void
	 */
	public function initialize(Server $server) {
		$this->server = $server;
		$server->on('method:PROPFIND', [$this, 'checkToS']);
		$server->on('method:PROPPATCH', [$this, 'checkToS']);
		$server->on('method:GET', [$this, 'checkToS']);
		$server->on('method:POST', [$this, 'checkToS']);
		$server->on('method:PUT', [$this, 'checkToS']);
		$server->on('method:DELETE', [$this, 'checkToS']);
		$server->on('method:MKCOL', [$this, 'checkToS']);
		$server->on('method:MOVE', [$this, 'checkToS']);
		$server->on('method:COPY', [$this, 'checkToS']);
		$server->on('method:REPORT', [$this, 'checkToS']);
	}

	/**
	 * Ask the checker if ToS have been signed
	 *
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @return bool
	 */
	public function checkToS(RequestInterface $request, ResponseInterface $response) {
		// we instantiate the checker here to make sure sabre auth backend was triggered
		$checker = \OC::$server->get(Checker::class);
		if (!$checker->currentUserHasSigned()) {
			throw new Forbidden($checker->getForbiddenMessage());
		}
		return true;
	}

	/**
	 * Returns a plugin name.
	 *
	 * Using this name other plugins will be able to access other plugins
	 * using \Sabre\DAV\Server::getPlugin
	 *
	 * @return string
	 */
	public function getPluginName() {
		return Application::APPNAME;
	}

	/**
	 * Returns a bunch of meta-data about the plugin.
	 *
	 * Providing this information is optional, and is mainly displayed by the
	 * Browser plugin.
	 *
	 * The description key in the returned array may contain html and will not
	 * be sanitized.
	 *
	 * @return array
	 */
	public function getPluginInfo() {
		return [
			'name'        => $this->getPluginName(),
			'description' => 'Check if terms of service have been signed before accepting a Dav request.',
		];
	}
}

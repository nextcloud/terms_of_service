<?php
/**
 * @copyright Copyright (c) 2024 Sagar Gurung  <sagargurung1001@gmail.com>
 *
 * @author Sagar Gurung  <sagargurung1001@gmail.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\TermsOfService\Events;

use OCP\EventDispatcher\Event;

/**
 * Class SignaturesResetEvent
 *
 * @package OCA\Terms_Of_Service\Events
 */
class SignaturesResetEvent extends Event {
	public function __construct() {
	}
}

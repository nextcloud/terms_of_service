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

namespace OCA\TermsAndConditions\Controller;

use OCA\TermsAndConditions\Db\Entities\Signatory;
use OCA\TermsAndConditions\Db\Mapper\SignatoryMapper;
use OCA\TermsAndConditions\Types\AccessTypes;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class SigningController extends Controller {
	/** @var string */
	private $userId;
	/** @var SignatoryMapper */
	private $signatoryMapper;

	public function __construct($appName,
								$UserId,
								IRequest $request,
								SignatoryMapper $signatoryMapper) {
		parent::__construct($appName, $request);
		$this->userId = $UserId;
		$this->signatoryMapper = $signatoryMapper;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $termId
	 * @return JSONResponse
	 */
	public function signLoginTerms($termId) {
		$signatory = new Signatory();
		$signatory->setUserId($this->userId);
		$signatory->setAccessType(AccessTypes::LOGIN);
		$signatory->setRemoteIp($this->request->getRemoteAddress());
		$signatory->setTermsId($termId);
		$signatory->setTimestamp(time());

		$this->signatoryMapper->insert($signatory);
		return new JSONResponse();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $termId
	 * @param int $shareId
	 * @return JSONResponse
	 */
	public function signInternalShare($termId, $shareId) {
		$signatory = new Signatory();
		$signatory->setUserId($this->userId);
		$signatory->setAccessType(AccessTypes::INTERNAL_SHARE);
		$signatory->setRemoteIp($this->request->getRemoteAddress());
		$signatory->setTermsId($termId);
		$signatory->setTimestamp(time());
		$signatory->setMetadata($shareId);

		// Also sign the login terms in case a user switched their country here
		$this->signLoginTerms($termId);

		$this->signatoryMapper->insert($signatory);
		return new JSONResponse();
	}

	/**
	 * @PublicPage
	 *
	 * @param int $termId
	 * @param string $publicShareId
	 * @return JSONResponse
	 */
	public function signPublicLinkShare($termId, $publicShareId) {
		$signatory = new Signatory();
		$signatory->setUserId('');
		$signatory->setAccessType(AccessTypes::PUBLIC_SHARE);
		$signatory->setRemoteIp($this->request->getRemoteAddress());
		$signatory->setTermsId($termId);
		$signatory->setTimestamp(time());
		$signatory->setMetadata($publicShareId);

		$this->signatoryMapper->insert($signatory);
		return new JSONResponse();
	}
}

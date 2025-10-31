<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TermsOfService\Db\Entities;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getTermsId()
 * @method void setTermsId(int $id)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method int getTimestamp()
 * @method void setTimestamp(int $timestamp)
 */
class Signatory extends Entity {
	/** @var int */
	public $termsId;
	/** @var string */
	public $userId;
	/** @var int */
	public $timestamp;
}

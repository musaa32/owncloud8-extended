<?php

/**
 * ownCloud
 *
 * @copyright (C) 2015 ownCloud, Inc.
 *
 * @author Bjoern Schiessle <schiessle@owncloud.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OC\Encryption;

class Util {

	protected $headerSize;

	public function __construct() {
		// the encryption header is exactly the same size as one block read
		// by a php stream;
		$this->headerSize = 8192;
	}

	/**
	 * read encryption module ID from header
	 *
	 * @param string $header
	 * @return string
	 */
	public function getEncryptionModuleId($header) {
		//TODO read encryption module ID from header and return
	}

	/**
	 * return size of encryption header
	 *
	 * @return integer
	 */
	public function getHeaderSize() {
		return $this->headerSize;
	}

}
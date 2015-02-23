<?php
/**
 * @author Robin McCorkell <rmccorkell@karoshi.org.uk>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OC\Memcache;

class Null extends Cache {
	public function get($key) {
		return null;
	}

	public function set($key, $value, $ttl = 0) {
		return true;
	}

	public function hasKey($key) {
		return false;
	}

	public function remove($key) {
		return true;
	}

	public function clear($prefix = '') {
		return true;
	}

	static public function isAvailable() {
		return true;
	}
}

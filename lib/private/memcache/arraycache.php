<?php
/**
 * @author Joas Schilling <nickvergessen@gmx.de>
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

class ArrayCache extends Cache {
	/** @var array Array with the cached data */
	protected $cachedData = array();

	/**
	 * {@inheritDoc}
	 */
	public function get($key) {
		if ($this->hasKey($key)) {
			return $this->cachedData[$key];
		}
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set($key, $value, $ttl = 0) {
		$this->cachedData[$key] = $value;
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasKey($key) {
		return isset($this->cachedData[$key]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove($key) {
		unset($this->cachedData[$key]);
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear($prefix = '') {
		if ($prefix === '') {
			$this->cachedData = [];
			return true;
		}

		foreach ($this->cachedData as $key => $value) {
			if (strpos($key, $prefix) === 0) {
				$this->remove($key);
			}
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	static public function isAvailable() {
		return true;
	}
}

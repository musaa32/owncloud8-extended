<?php
/**
 * @author Bjoern Schiessle <schiessle@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Scrutinizer Auto-Fixer <auto-fixer@scrutinizer-ci.com>
 * @author Vincent Petry <pvince81@owncloud.com>
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
namespace OC\Files\Storage;

use OCP\Files\Storage\IStorageFactory;

class StorageFactory implements IStorageFactory {
	/**
	 * @var callable[] $storageWrappers
	 */
	private $storageWrappers = array();

	/**
	 * allow modifier storage behaviour by adding wrappers around storages
	 *
	 * $callback should be a function of type (string $mountPoint, Storage $storage) => Storage
	 *
	 * @param string $wrapperName name of the wrapper
	 * @param callable $callback callback
	 * @param \OCP\Files\Mount\IMountPoint[] $existingMounts existing mount points to apply the wrapper to
	 * @return bool true if the wrapper was added, false if there was already a wrapper with this
	 * name registered
	 */
	public function addStorageWrapper($wrapperName, $callback, $existingMounts = []) {
		if (isset($this->storageWrappers[$wrapperName])) {
			return false;
		}

		// apply to existing mounts before registering it to prevent applying it double in MountPoint::createStorage
		foreach ($existingMounts as $mount) {
			$mount->wrapStorage($callback);
		}

		$this->storageWrappers[$wrapperName] = $callback;
		return true;
	}

	/**
	 * Remove a storage wrapper by name.
	 * Note: internal method only to be used for cleanup
	 *
	 * @param string $wrapperName name of the wrapper
	 * @internal
	 */
	public function removeStorageWrapper($wrapperName) {
		unset($this->storageWrappers[$wrapperName]);
	}

	/**
	 * Create an instance of a storage and apply the registered storage wrappers
	 *
	 * @param string|boolean $mountPoint
	 * @param string $class
	 * @param array $arguments
	 * @return \OCP\Files\Storage
	 */
	public function getInstance($mountPoint, $class, $arguments) {
		return $this->wrap($mountPoint, new $class($arguments));
	}

	/**
	 * @param string|boolean $mountPoint
	 * @param \OCP\Files\Storage $storage
	 * @return \OCP\Files\Storage
	 */
	public function wrap($mountPoint, $storage) {
		foreach ($this->storageWrappers as $wrapper) {
			$storage = $wrapper($mountPoint, $storage);
		}
		return $storage;
	}
}

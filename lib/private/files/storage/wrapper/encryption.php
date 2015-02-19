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

namespace OC\Files\Storage\Wrapper;

class Encryption extends Wrapper {

	/** @var string */
	private $mountPoint;

	private $util;

	/** @var \OCP\Encryption\IManager */
	private $encryptionManager;

	/**
	 * @param array $parameters
	 */
	function __construct($parameters, \OCP\Encryption\IManager $encryptionManager = null, $util = null) {

		$this->mountPoint = $parameters['mountPoint'];
		parent::__construct($parameters);

		if ($util) {
			$this->util = $util;
		} else {
			//TODO create new util class
		}

		if ($encryptionManager) {
			$this->encryptionManager = $encryptionManager;
		} else {
			$this->encryptionManager = \OC::$server->getEncryptionManager();
		}
	}

	/**
	 * see http://php.net/manual/en/function.filesize.php
	 * The result for filesize when called on a folder is required to be 0
	 *
	 * @param string $path
	 * @return int
	 */
	public function filesize($path) {
		$fullPath = $this->getFullPath($path);
		$fileInfo = \OC\Files\Filesystem::getFileInfo($fullPath);
		$size = $fileInfo->getSize();
		if ($fileInfo->getSize() > 0 && $fileInfo->isEncrypted()) {
			$size = $fileInfo->getUnencryptedSize();
			if ($size <= 0) {
				$encryptionModule = $this->getEncryptionModule($fullPath);
				$size = $encryptionModule->calculateUnencryptedSize($fullPath);
				\OC\Files\Filesystem::putFileInfo($fullPath, array('unencrypted_size' => $size));
			}

		}

		return $size;
	}

	/**
	 * see http://php.net/manual/en/function.file_get_contents.php
	 *
	 * @param string $path
	 * @return string
	 */
	public function file_get_contents($path) {
		// todo drecrypt data
		return $this->storage->file_get_contents($path);
	}

	/**
	 * see http://php.net/manual/en/function.file_put_contents.php
	 *
	 * @param string $path
	 * @param string $data
	 * @return bool
	 */
	public function file_put_contents($path, $data) {
		//todo encrypt data
		return $this->storage->file_put_contents($path, $data);
	}

	/**
	 * see http://php.net/manual/en/function.unlink.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function unlink($path) {
		//todo remove encryption keys
		return $this->storage->unlink($path);
	}

	/**
	 * see http://php.net/manual/en/function.rename.php
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return bool
	 */
	public function rename($path1, $path2) {
		// todo rename encryption keys, get users with access to the file and reencrypt
		// or is this to encryption module specific? Then we can hand this over
		return $this->storage->rename($path1, $path2);
	}

	/**
	 * see http://php.net/manual/en/function.copy.php
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return bool
	 */
	public function copy($path1, $path2) {
		// todo copy encryption keys, get users with access to the file and reencrypt
		// or is this to encryption module specific? Then we can hand this over
		return $this->storage->copy($path1, $path2);
	}

	/**
	 * see http://php.net/manual/en/function.fopen.php
	 *
	 * @param string $path
	 * @param string $mode
	 * @return resource
	 */
	public function fopen($path, $mode) {
		return $this->storage->fopen($path, $mode);
	}

	/**
	 * return full path, including mount point
	 *
	 * @param string $path relative to mount point
	 * @return string full path including mount point
	 */
	protected function getFullPath($path) {
		return Filesystem::normalizePath($this->mountPoint . '/' . $path);
	}

	/**
	 * get encryption module needed to read/write the file located at $path
	 *
	 * @param string $path
	 * @return \OCP\Encryption\IEncryptionModule
	 */
	protected function getEncryptionModule($path) {
		$encryptionModuleId = $this->util->getEncryptionModuleId($path);
		return $this->encryptionManager->getEncryptionModule($encryptionModuleId);
	}

}

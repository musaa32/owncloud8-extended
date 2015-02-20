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

	/** @var \OC\Encryption\Util */
	private $util;

	/** @var \OCP\Encryption\IManager */
	private $encryptionManager;

	/**
	 * @param array $parameters
	 * @param \OCP\Encryption\IManager $encryptionManager
	 */
	function __construct($parameters, \OCP\Encryption\IManager $encryptionManager, \OC\Encryption\Util $util) {
		$this->mountPoint = $parameters['mountPoint'];
		$this->encryptionManager = $encryptionManager;
		$this->util = $util;
		parent::__construct($parameters);
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

		$info  = $this->getCache()->get($path);
		$size = $info['size'];
		if($size > 0 && $info['encrypted']) {
			$size = $info['unencrypted_size'];
			if ($size <= 0) {
				$encryptionModule = $this->getEncryptionModule($path);
				$size = $encryptionModule->calculateUnencryptedSize($fullPath);
				$this->getCache()->update($info['id'], array('unencrypted_size' => $size));
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

		$data = null;
		$encryptionModule = $this->getEncryptionModule($path);

		if ($encryptionModule) {

			$handle = $this->fopen($path, 'r');

			if (is_resource($handle)) {
				while (($plainDataChunk = fgets($handle, $this->util->getBlockSize())) !== false) {
					$data .= $plainDataChunk;
				}
			}

		} else {
			$data = $this->storage->file_get_contents($path);
		}

		return $data;

	}

	/**
	 * see http://php.net/manual/en/function.file_put_contents.php
	 *
	 * @param string $path
	 * @param string $data
	 * @return bool
	 */
	public function file_put_contents($path, $data) {

		$fullPath = $this->getFullPath($path);
		$unencryptedSize = sizeof($data);

		if ($this->storage->file_exists($path)) {
			$encryptionModule = $this->getEncryptionModule($path);
		} else {
			$encryptionModule = $this->encryptionManager->getEncryptionModule();
		}


		if ($encryptionModule->shouldEncrypt($fullPath)) {

			$headerData = $encryptionModule->begin($fullPath, $this->getHeader($path));
			$encryptedData = $this->util->createHeader($headerData, $encryptionModule);
			$accessList = $this->util->getSharingUsersArray($fullPath);

			$blockSize = $this->util->getBlockSize();
			$start = 0;
			do {
				$rawData = mb_strcut($data, $start, $blockSize);
				$encryptedData .= $encryptionModule->encrypt($rawData, $accessList);
				$start = $start + $blockSize;
			} while ($rawData);

			$remainingData = $encryptionModule->end($fullPath);
			if ($remainingData) {
				$encryptedData .= $remainingData;
			}

			$data = $encryptedData;
		}

		$info  = $this->getCache()->get($path);
		if (isset($info['id'])) {
			$this->getCache()->update($info['id'], array('unencrypted_size' => $unencryptedSize));
		} else {
			//TODO how to set unencrypted size for a new file, not yet indexed?
		}

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
		// todo call encryption stream wrapper
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
	 * read header from file
	 *
	 * @param string $path
	 * @return array
	 */
	protected function getHeader($path) {
		$handle = $this->storage->fopen($path, 'r');
		$header = fread($handle, $this->util->getHeaderSize());
		fclose($handle);
		return $this->util->readHeader($header);
	}

	/**
	 * read encryption module needed to read/write the file located at $path
	 *
	 * @param string $path
	 * @return \OCP\Encryption\IEncryptionModule|null
	 */
	protected function getEncryptionModule($path) {
		$rawHeader = $this->getHeader($path);
		$header = $this->util->readHeader($rawHeader);
		$encryptionModuleId = $this->util->getEncryptionModuleId($header);
		return $this->encryptionManager->getEncryptionModule($encryptionModuleId);
	}

}

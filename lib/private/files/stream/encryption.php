<?php

/**
 * ownCloud - Encryption stream wrapper
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

namespace OC\Files\Stream;

use OCA\Files_Encryption\Exception\EncryptionException;

/**
 * Provides 'crypt://' stream wrapper protocol.
 * @note We use a stream wrapper because it is the most secure way to handle
 * decrypted content transfers. There is no safe way to decrypt the entire file
 * somewhere on the server, so we have to encrypt and decrypt blocks on the fly.
 * @note Paths used with this protocol MUST BE RELATIVE. Use URLs like:
 * crypt://filename, or crypt://subdirectory/filename, NOT
 * crypt:///home/user/owncloud/data. Otherwise keyfiles will be put in
 * [owncloud]/data/user/files_encryption/keyfiles/home/user/owncloud/data and
 * will not be accessible to other methods.
 * @note Data read and written must always be 8192 bytes long, as this is the
 * buffer size used internally by PHP. The encryption process makes the input
 * data longer, and input is chunked into smaller pieces in order to result in
 * a 8192 encrypted block size.
 * @note When files are deleted via webdav, or when they are updated and the
 * previous version deleted, this is handled by OC\Files\View, and thus the
 * encryption proxies are used and keyfiles deleted.
 */
class Encryption {

	const PADDING_CHAR = '-';

	/**
	 *
	 * @var OCP\Encryption\IEncryptionModule
	 */
	private $module;


	/**
	 * @param string $path raw path relative to data/
	 * @param string $mode
	 * @param int $options
	 * @param string $opened_path
	 * @return bool
	 * @throw \OCA\Files_Encryption\Exception\EncryptionException
	 */
	public function stream_open($path, $mode, $options, &$opened_path) {

		// TODO read header
		// get encryption module from header
		// get encryption module from encryption manager
		// get list of users with access to the file from the share api, at least for write operations
		// for write call shouldEncrypt() of encryption modul
		// call start() for the encryption modul


		return is_resource($this->handle);

	}

	private function readHeader() {

		if ($this->isLocalTmpFile) {
			$handle = fopen($this->localTmpFile, 'r');
		} else {
			$handle = $this->rootView->fopen($this->rawPath, 'r');
		}

		if (is_resource($handle)) {
			$data = fread($handle, Crypt::BLOCKSIZE);

			$header = Crypt::parseHeader($data);
			$this->module = Crypt::getEncryptionModule($header);

			// remeber that we found a header
			if (!empty($header)) {
				$this->containHeader = true;
			}

			fclose($handle);
		}
	}

	/**
	 * Returns the current position of the file pointer
	 * @return int position of the file pointer
	 */
	public function stream_tell() {
		return ftell($this->handle);
	}

	/**
	 * @param int $offset
	 * @param int $whence
	 * @return bool true if fseek was successful, otherwise false
	 */
	public function stream_seek($offset, $whence = SEEK_SET) {

		// TODO need some fseek logic here
		// TODO maybe ask encryption module for block size or other information needed
		// TODO depends also on the implementation here https://github.com/owncloud/core/pull/12909

	}

	/**
	 * @param int $count
	 * @return bool|string
	 * @throws \OCA\Files_Encryption\Exception\EncryptionException
	 */
	public function stream_read($count) {

		$this->writeCache = '';

		if ($count !== Crypt::BLOCKSIZE) {
			\OCP\Util::writeLog('Encryption library', 'PHP "bug" 21641 no longer holds, decryption system requires refactoring', \OCP\Util::FATAL);
			throw new EncryptionException('expected a block size of 8192 byte', EncryptionException::UNEXPECTED_BLOCK_SIZE);
		}

		// Get the data from the file handle
		$data = fread($this->handle, $count);

		// if this block contained the header we move on to the next block
		if (Crypt::isHeader($data)) {
			$data = fread($this->handle, $count);
		}

		// TODO call decrypt($data) from encryption module to get the result

		return $result;

	}


	/**
	 * write header at beginning of encrypted file
	 *
	 * @throws \OCA\Files_Encryption\Exception\EncryptionException
	 */
	private function writeHeader() {

		$header = Crypt::generateHeader();

		if (strlen($header) > Crypt::BLOCKSIZE) {
			throw new EncryptionException('max header size exceeded', EncryptionException::ENCRYPTION_HEADER_TO_LARGE);
		}

		$paddedHeader = str_pad($header, Crypt::BLOCKSIZE, self::PADDING_CHAR, STR_PAD_RIGHT);

		fwrite($this->handle, $paddedHeader);
		$this->headerWritten = true;
	}

	/**
	 * Handle plain data from the stream, and write it in 8192 byte blocks
	 * @param string $data data to be written to disk
	 * @note the data will be written to the path stored in the stream handle, set in stream_open()
	 * @note $data is only ever be a maximum of 8192 bytes long. This is set by PHP internally. stream_write() is called multiple times in a loop on data larger than 8192 bytes
	 * @note Because the encryption process used increases the length of $data, a writeCache is used to carry over data which would not fit in the required block size
	 * @note Padding is added to each encrypted block to ensure that the resulting block is exactly 8192 bytes. This is removed during stream_read
	 * @note PHP automatically updates the file pointer after writing data to reflect it's length. There is generally no need to update the poitner manually using fseek
	 */
	public function stream_write($data) {

		//TODO call encrypt() from encryption module
		//TODO track unencryped size


	}


	/**
	 * @param int $option
	 * @param int $arg1
	 * @param int|null $arg2
	 */
	public function stream_set_option($option, $arg1, $arg2) {
		$return = false;
		switch ($option) {
			case STREAM_OPTION_BLOCKING:
				$return = stream_set_blocking($this->handle, $arg1);
				break;
			case STREAM_OPTION_READ_TIMEOUT:
				$return = stream_set_timeout($this->handle, $arg1, $arg2);
				break;
			case STREAM_OPTION_WRITE_BUFFER:
				$return = stream_set_write_buffer($this->handle, $arg1);
		}

		return $return;
	}

	/**
	 * @return array
	 */
	public function stream_stat() {
		return fstat($this->handle);
	}

	/**
	 * @param int $mode
	 */
	public function stream_lock($mode) {
		return flock($this->handle, $mode);
	}

	/**
	 * @return bool
	 */
	public function stream_flush() {

		return fflush($this->handle);
		// Not a typo: http://php.net/manual/en/function.fflush.php

	}

	/**
	 * @return bool
	 */
	public function stream_eof() {
		return feof($this->handle);
	}


	/**
	 * @return bool
	 */
	public function stream_close() {

		// TODO call end() of encryption module so that it can perform some final operation
		//      wirte last chunk of data, encrypt the file key with the users public keys, etc
		// TODO write unencrpted size to filecache


		$result = fclose($this->handle);

		return $result;

	}

}

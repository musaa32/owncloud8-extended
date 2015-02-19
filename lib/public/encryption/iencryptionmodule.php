<?php

/**
 * ownCloud - public interface of ownCloud for encryption modules
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

namespace OCP\Encryption;

interface IEncryptionModule {

	/**
	 * @return string defining the technical unique key
	 */
	public function getKey();

	/**
	 * In comparison to getKey() this function returns a human readable (maybe translated) name
	 *
	 * @return mixed
	 */
	public function getDisplayName();

	/**
	 * start receiving chungs from a file. This is the place where you can
	 * perfom some initial step before starting encrypting/decrypting the
	 * chunks
	 *
	 * @param string $path to the file
	 * @param array $header contains the header data read from the file
	 *
	 * $return array $header optional in case of a write operation the array
	 *                       contain data which should be written to the header
	 */
	public function begin($path, $header);

	/**
	 * last chunk received. This is the place where you can perform some final
	 * operation and return some remaining data if something is left in your
	 * buffer.
	 *
	 * @param string $path to the file
	 */
	public function end($path);

	/**
	 * encrypt data
	 *
	 * @param string $data you want to encrypt
	 * @param array $users list of users who should be able to access the file
	 * @param array $groups list of groups which should be able to access the file
	 * @return mixed encrypted data
	 */
	public function encrypt($data, $users, $groups);

	/**
	 * decrypt data
	 *
	 * @param string $data you want to decrypt
	 * @param string $user decrypt as user
	 * @return mixed decrypted data
	 */
	public function decrypt($data, $user);

	/**
	 * update encrypted file, e.g. give additional users access to the file
	 *
	 * @param string $path path to the file which should be updated
	 * @param string $users list of user who should have access to the file
	 * @return boolean
	 */
	public function update($path, $users, $groups);

	/**
	 * should the file be encrypted or not
	 *
	 * @param string $path
	 * @return boolean
	 */
	public function shouldEncrypt($path);

	/**
	 * calculate unencrypted size
	 *
	 * @param string $path to file
	 * @return integer unencrypted size
	 */
	public function calculateUnencryptedSize($path);
}

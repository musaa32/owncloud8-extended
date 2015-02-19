<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Storage;

use OCA\Files_External\Flysystem;
use OCA\Files_External\FtpAdapter;

class FTP extends Flysystem {
	private $password;
	private $user;
	private $host;
	private $secure;

	/**
	 * @var \League\Flysystem\Adapter\Ftp
	 */
	private $adapter;

	public function __construct($params) {
		if (isset($params['host']) && isset($params['user']) && isset($params['password'])) {
			$this->host = $params['host'];
			$this->user = $params['user'];
			$this->password = $params['password'];
			if (isset($params['secure'])) {
				if (is_string($params['secure'])) {
					$this->secure = ($params['secure'] === 'true');
				} else {
					$this->secure = (bool)$params['secure'];
				}
			} else {
				$this->secure = false;
			}
			$this->root = isset($params['root']) ? $params['root'] : '/';
			if (!$this->root || $this->root[0] != '/') {
				$this->root = '/' . $this->root;
			}
			if (substr($this->root, -1) !== '/') {
				$this->root .= '/';
			}
			$this->adapter = new FtpAdapter([
				'host' => $this->host,
				'username' => $this->user,
				'password' => $this->password,
				'ssl' => $this->secure
			]);
			$this->buildFlySystem($this->adapter);
		} else {
			throw new \Exception('Creating \OC\Files\Storage\FTP storage failed');
		}

	}

	public function getId() {
		return 'ftp::' . $this->user . '@' . $this->host . '/' . $this->root;
	}

	/**
	 * check if php-ftp is installed
	 */
	public static function checkDependencies() {
		if (function_exists('ftp_login')) {
			return (true);
		} else {
			return array('ftp');
		}
	}

	public function copy($source, $target) {
		if ($this->is_dir($source)) {
			if ($this->file_exists($target)) {
				$this->unlink($target);
			}
			return $this->copyRecursive($this->buildPath($source), $this->buildPath($target));
		} else {
			return parent::copy($source, $target);
		}
	}

	public function disconnect() {
		$this->adapter->disconnect();
	}

	public function __destruct() {
		$this->disconnect();
	}
}

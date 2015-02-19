<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files\Storage;

class FTP extends Storage {
	private $config;

	protected function setUp() {
		parent::setUp();

		$id = $this->getUniqueID();
		$this->config = include('files_external/tests/config.ftp.php');
		if ( ! is_array($this->config) or ! $this->config['run']) {
			$this->markTestSkipped('FTP backend not configured');
		}
		$this->config['root'] .= '/' . $id; //make sure we have an new empty folder to work in
		$this->instance = new \OC\Files\Storage\FTP($this->config);
		$this->assertTrue($this->instance->mkdir(''));
	}

	protected function tearDown() {
		if ($this->instance) {
			$this->instance->rmdir('');
			$this->instance->disconnect();
		}

		parent::tearDown();
	}
}

<?php
/**
 * @author Andreas Fischer <bantu@owncloud.com>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
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
// Init owncloud
global $eventSource;

\OCP\JSON::checkLoggedIn();
\OCP\JSON::callCheck();

\OC::$server->getSession()->close();

// Get the params
$dir = isset( $_REQUEST['dir'] ) ? '/'.trim((string)$_REQUEST['dir'], '/\\') : '';
$filename = isset( $_REQUEST['filename'] ) ? trim((string)$_REQUEST['filename'], '/\\') : '';
$content = isset( $_REQUEST['content'] ) ? (string)$_REQUEST['content'] : '';
$source = isset( $_REQUEST['source'] ) ? trim((string)$_REQUEST['source'], '/\\') : '';

if($source) {
	$eventSource = \OC::$server->createEventSource();
}

function progress($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {
	static $filesize = 0;
	static $lastsize = 0;
	global $eventSource;

	switch($notification_code) {
		case STREAM_NOTIFY_FILE_SIZE_IS:
			$filesize = $bytes_max;
			break;

		case STREAM_NOTIFY_PROGRESS:
			if ($bytes_transferred > 0) {
				if (!isset($filesize) || $filesize === 0) {
				} else {
					$progress = (int)(($bytes_transferred/$filesize)*100);
					if($progress>$lastsize) { //limit the number or messages send
						$eventSource->send('progress', $progress);
					}
					$lastsize=$progress;
				}
			}
			break;
	}
}


$l10n = \OC::$server->getL10N('files');

$result = array(
	'success' 	=> false,
	'data'		=> NULL
);
$trimmedFileName = trim($filename);

if($trimmedFileName === '') {
	$result['data'] = array('message' => (string)$l10n->t('File name cannot be empty.'));
	OCP\JSON::error($result);
	exit();
}
if($trimmedFileName === '.' || $trimmedFileName === '..') {
	$result['data'] = array('message' => (string)$l10n->t('"%s" is an invalid file name.', $trimmedFileName));
	OCP\JSON::error($result);
	exit();
}

if(!OCP\Util::isValidFileName($filename)) {
	$result['data'] = array('message' => (string)$l10n->t("Invalid name, '\\', '/', '<', '>', ':', '\"', '|', '?' and '*' are not allowed."));
	OCP\JSON::error($result);
	exit();
}

if (!\OC\Files\Filesystem::file_exists($dir . '/')) {
	$result['data'] = array('message' => (string)$l10n->t(
			'The target folder has been moved or deleted.'),
			'code' => 'targetnotfound'
		);
	OCP\JSON::error($result);
	exit();
}

$target = $dir.'/'.$filename;

if (\OC\Files\Filesystem::file_exists($target)) {
	$result['data'] = array('message' => (string)$l10n->t(
			'The name %s is already used in the folder %s. Please choose a different name.',
			array($filename, $dir))
		);
	OCP\JSON::error($result);
	exit();
}

if($source) {
	$httpHelper = \OC::$server->getHTTPHelper();
	if(!$httpHelper->isHTTPURL($source)) {
		OCP\JSON::error(array('data' => array('message' => $l10n->t('Not a valid source'))));
		exit();
	}

	if (!ini_get('allow_url_fopen')) {
		$eventSource->send('error', array('message' => $l10n->t('Server is not allowed to open URLs, please check the server configuration')));
		$eventSource->close();
		exit();
	}

	$source = $httpHelper->getFinalLocationOfURL($source);

	$ctx = stream_context_create(\OC::$server->getHTTPHelper()->getDefaultContextArray(), array('notification' =>'progress'));

	$sourceStream=@fopen($source, 'rb', false, $ctx);
	$result = 0;
	if (is_resource($sourceStream)) {
		$meta = stream_get_meta_data($sourceStream);
		if (isset($meta['wrapper_data']) && is_array($meta['wrapper_data'])) {
			//check stream size
			$storageStats = \OCA\Files\Helper::buildFileStorageStatistics($dir);
			$freeSpace = $storageStats['freeSpace'];

			foreach($meta['wrapper_data'] as $header) {
				if (strpos($header, ':') === false){
					continue;
				}
				list($name, $value) = explode(':', $header);
				if ('content-length' === strtolower(trim($name))) {
					$length = (int) trim($value);

					if ($length > $freeSpace) {
						$delta = $length - $freeSpace;
						$humanDelta = OCP\Util::humanFileSize($delta);

						$eventSource->send('error', array('message' => (string)$l10n->t('The file exceeds your quota by %s', array($humanDelta))));
						$eventSource->close();
						fclose($sourceStream);
						exit();
					}
				}
			}
		}
		$result = \OC\Files\Filesystem::file_put_contents($target, $sourceStream);
	}
	if($result) {
		$meta = \OC\Files\Filesystem::getFileInfo($target);
		$data = \OCA\Files\Helper::formatFileInfo($meta);
		$eventSource->send('success', $data);
	} else {
		$eventSource->send('error', array('message' => $l10n->t('Error while downloading %s to %s', array($source, $target))));
	}
	if (is_resource($sourceStream)) {
		fclose($sourceStream);
	}
	$eventSource->close();
	exit();
} else {
	$success = false;
	if (!$content) {
		$templateManager = OC_Helper::getFileTemplateManager();
		$mimeType = OC_Helper::getMimetypeDetector()->detectPath($target);
		$content = $templateManager->getTemplate($mimeType);
	}

	if($content) {
		$success = \OC\Files\Filesystem::file_put_contents($target, $content);
	} else {
		$success = \OC\Files\Filesystem::touch($target);
	}

	if($success) {
		$meta = \OC\Files\Filesystem::getFileInfo($target);
		OCP\JSON::success(array('data' => \OCA\Files\Helper::formatFileInfo($meta)));
		exit();
	}
}

OCP\JSON::error(array('data' => array( 'message' => $l10n->t('Error when creating the file') )));

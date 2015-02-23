<?php
/**
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Georg Ehrke <georg@ownCloud.com>
 * @author Joas Schilling <nickvergessen@gmx.de>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Robin Appelman <icewind@owncloud.com>
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
namespace OC\Preview;

abstract class Provider {
	private $options;

	public function __construct($options) {
		$this->options = $options;
	}

	/**
	 * @return string Regex with the mimetypes that are supported by this provider
	 */
	abstract public function getMimeType();

	/**
	 * Check if a preview can be generated for $path
	 *
	 * @param \OC\Files\FileInfo $file
	 * @return bool
	 */
	public function isAvailable($file) {
		return true;
	}

	/**
	 * get thumbnail for file at path $path
	 * @param string $path Path of file
	 * @param int $maxX The maximum X size of the thumbnail. It can be smaller depending on the shape of the image
	 * @param int $maxY The maximum Y size of the thumbnail. It can be smaller depending on the shape of the image
	 * @param bool $scalingup Disable/Enable upscaling of previews
	 * @param \OC\Files\View $fileview fileview object of user folder
	 * @return mixed
	 * 		false if no preview was generated
	 *		OC_Image object of the preview
	 */
	abstract public function getThumbnail($path, $maxX, $maxY, $scalingup, $fileview);
}

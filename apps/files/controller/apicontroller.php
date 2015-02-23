<?php
/**
 * @author Joas Schilling <nickvergessen@gmx.de>
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Tobias Kaminsky <tobias@kaminsky.me>
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
namespace OCA\Files\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DownloadResponse;
use OC\Preview;
use OCA\Files\Service\TagService;

/**
 * Class ApiController
 *
 * @package OCA\Files\Controller
 */
class ApiController extends Controller {
	/** @var TagService */
	private $tagService;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param TagService $tagService
	 */
	public function __construct($appName,
								IRequest $request,
								TagService $tagService){
		parent::__construct($appName, $request);
		$this->tagService = $tagService;
	}

	/**
	 * Gets a thumbnail of the specified file
	 *
	 * @since API version 1.0
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $x
	 * @param int $y
	 * @param string $file URL-encoded filename
	 * @return JSONResponse|DownloadResponse
	 */
	public function getThumbnail($x, $y, $file) {
		if($x < 1 || $y < 1) {
			return new JSONResponse('Requested size must be numeric and a positive value.', Http::STATUS_BAD_REQUEST);
		}

		try {
			$preview = new Preview('', 'files', urldecode($file), $x, $y, true);
			echo($preview->showPreview('image/png'));
			return new DownloadResponse(urldecode($file).'.png', 'image/png');
		} catch (\Exception $e) {
			return new JSONResponse('File not found.', Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Updates the info of the specified file path
	 * The passed tags are absolute, which means they will
	 * replace the actual tag selection.
	 *
	 * @NoAdminRequired
	 * @CORS
	 *
	 * @param string $path path
	 * @param array|string $tags array of tags
	 * @return DataResponse
	 */
	public function updateFileTags($path, $tags = null) {
		$result = [];
		// if tags specified or empty array, update tags
		if (!is_null($tags)) {
			try {
				$this->tagService->updateFileTags($path, $tags);
			} catch (\OCP\Files\NotFoundException $e) {
				return new DataResponse([
					'message' => $e->getMessage()
				], Http::STATUS_NOT_FOUND);
			} catch (\OCP\Files\StorageNotAvailableException $e) {
				return new DataResponse([
					'message' => $e->getMessage()
				], Http::STATUS_SERVICE_UNAVAILABLE);
			} catch (\Exception $e) {
				return new DataResponse([
					'message' => $e->getMessage()
				], Http::STATUS_NOT_FOUND);
			}
			$result['tags'] = $tags;
		}
		return new DataResponse($result);
	}

	/**
	 * Returns a list of all files tagged with the given tag.
	 *
	 * @NoAdminRequired
	 * @CORS
	 *
	 * @param array|string $tagName tag name to filter by
	 * @return DataResponse
	 */
	public function getFilesByTag($tagName) {
		$files = array();
		$fileInfos = $this->tagService->getFilesByTag($tagName);
		foreach ($fileInfos as &$fileInfo) {
			$file = \OCA\Files\Helper::formatFileInfo($fileInfo);
			$parts = explode('/', dirname($fileInfo->getPath()), 4);
			if(isset($parts[3])) {
				$file['path'] = '/' . $parts[3];
			} else {
				$file['path'] = '/';
			}
			$file['tags'] = [$tagName];
			$files[] = $file;
		}
		return new DataResponse(['files' => $files]);
	}

}

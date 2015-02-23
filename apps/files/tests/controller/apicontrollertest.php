<?php
/**
 * @author Lukas Reschke <lukas@owncloud.com>
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

use OC\Files\FileInfo;
use OCP\AppFramework\Http;
use OC\Preview;
use OCP\Files\NotFoundException;
use OCP\Files\StorageNotAvailableException;
use Test\TestCase;
use OCP\IRequest;
use OCA\Files\Service\TagService;
use OCP\AppFramework\Http\DataResponse;

/**
 * Class ApiController
 *
 * @package OCA\Files\Controller
 */
class ApiControllerTest extends TestCase {
	/** @var string */
	private $appName = 'files';
	/** @var IRequest */
	private $request;
	/** @var TagService */
	private $tagService;
	/** @var ApiController */
	private $apiController;

	public function setUp() {
		$this->request = $this->getMockBuilder('\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->tagService = $this->getMockBuilder('\OCA\Files\Service\TagService')
			->disableOriginalConstructor()
			->getMock();

		$this->apiController = new ApiController(
			$this->appName,
			$this->request,
			$this->tagService
		);
	}

	public function testGetFilesByTagEmpty() {
		$tagName = 'MyTagName';
		$this->tagService->expects($this->once())
			->method('getFilesByTag')
			->with($this->equalTo([$tagName]))
			->will($this->returnValue([]));

		$expected = new DataResponse(['files' => []]);
		$this->assertEquals($expected, $this->apiController->getFilesByTag([$tagName]));
	}

	public function testGetFilesByTagSingle() {
		$tagName = 'MyTagName';
		$fileInfo = new FileInfo(
			'/root.txt',
			$this->getMockBuilder('\OC\Files\Storage\Storage')
				->disableOriginalConstructor()
				->getMock(),
			'/var/www/root.txt',
			[
				'mtime' => 55,
				'mimetype' => 'application/pdf',
				'size' => 1234,
				'etag' => 'MyEtag',
			],
			$this->getMockBuilder('\OCP\Files\Mount\IMountPoint')
				->disableOriginalConstructor()
				->getMock()
		);
		$this->tagService->expects($this->once())
			->method('getFilesByTag')
			->with($this->equalTo([$tagName]))
			->will($this->returnValue([$fileInfo]));

		$expected = new DataResponse([
			'files' => [
				[
					'id' => null,
					'parentId' => null,
					'date' => 'January 1, 1970 at 12:00:55 AM GMT+0',
					'mtime' => 55000,
					'icon' => \OCA\Files\Helper::determineIcon($fileInfo),
					'name' => 'root.txt',
					'permissions' => null,
					'mimetype' => 'application/pdf',
					'size' => 1234,
					'type' => 'file',
					'etag' => 'MyEtag',
					'path' => '/',
					'tags' => [
						[
							'MyTagName'
						]
					],
				],
			],
		]);
		$this->assertEquals($expected, $this->apiController->getFilesByTag([$tagName]));
	}

	public function testGetFilesByTagMultiple() {
		$tagName = 'MyTagName';
		$fileInfo1 = new FileInfo(
			'/root.txt',
			$this->getMockBuilder('\OC\Files\Storage\Storage')
				->disableOriginalConstructor()
				->getMock(),
			'/var/www/root.txt',
			[
				'mtime' => 55,
				'mimetype' => 'application/pdf',
				'size' => 1234,
				'etag' => 'MyEtag',
			],
			$this->getMockBuilder('\OCP\Files\Mount\IMountPoint')
				->disableOriginalConstructor()
				->getMock()
		);
		$fileInfo2 = new FileInfo(
			'/root.txt',
			$this->getMockBuilder('\OC\Files\Storage\Storage')
				->disableOriginalConstructor()
				->getMock(),
			'/var/www/some/sub.txt',
			[
				'mtime' => 999,
				'mimetype' => 'application/binary',
				'size' => 9876,
				'etag' => 'SubEtag',
			],
			$this->getMockBuilder('\OCP\Files\Mount\IMountPoint')
				->disableOriginalConstructor()
				->getMock()
		);
		$this->tagService->expects($this->once())
			->method('getFilesByTag')
			->with($this->equalTo([$tagName]))
			->will($this->returnValue([$fileInfo1, $fileInfo2]));

		$expected = new DataResponse([
			'files' => [
				[
					'id' => null,
					'parentId' => null,
					'date' => 'January 1, 1970 at 12:00:55 AM GMT+0',
					'mtime' => 55000,
					'icon' => \OCA\Files\Helper::determineIcon($fileInfo1),
					'name' => 'root.txt',
					'permissions' => null,
					'mimetype' => 'application/pdf',
					'size' => 1234,
					'type' => 'file',
					'etag' => 'MyEtag',
					'path' => '/',
					'tags' => [
						[
							'MyTagName'
						]
					],
				],
				[
					'id' => null,
					'parentId' => null,
					'date' => 'January 1, 1970 at 12:16:39 AM GMT+0',
					'mtime' => 999000,
					'icon' => \OCA\Files\Helper::determineIcon($fileInfo2),
					'name' => 'root.txt',
					'permissions' => null,
					'mimetype' => 'application/binary',
					'size' => 9876,
					'type' => 'file',
					'etag' => 'SubEtag',
					'path' => '/',
					'tags' => [
						[
							'MyTagName'
						]
					],
				]
			],
		]);
		$this->assertEquals($expected, $this->apiController->getFilesByTag([$tagName]));
	}

	public function testUpdateFileTagsEmpty() {
		$expected = new DataResponse([]);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt'));
	}

	public function testUpdateFileTagsWorking() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2']);

		$expected = new DataResponse([
			'tags' => [
				'Tag1',
				'Tag2'
			],
		]);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsNotFoundException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new NotFoundException('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsStorageNotAvailableException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new StorageNotAvailableException('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_SERVICE_UNAVAILABLE);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}

	public function testUpdateFileTagsStorageGenericException() {
		$this->tagService->expects($this->once())
			->method('updateFileTags')
			->with('/path.txt', ['Tag1', 'Tag2'])
			->will($this->throwException(new \Exception('My error message')));

		$expected = new DataResponse(['message' => 'My error message'], Http::STATUS_NOT_FOUND);
		$this->assertEquals($expected, $this->apiController->updateFileTags('/path.txt', ['Tag1', 'Tag2']));
	}
}

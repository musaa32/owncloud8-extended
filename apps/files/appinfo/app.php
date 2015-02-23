<?php
/**
 * @author Felix Moeller <mail@felixmoeller.de>
 * @author Jakob Sack <mail@jakobsack.de>
 * @author Joas Schilling <nickvergessen@gmx.de>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Tom Needham <tom@owncloud.com>
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
$l = \OC::$server->getL10N('files');

OCP\App::registerAdmin('files', 'admin');

OCP\App::addNavigationEntry(array("id" => "files_index",
	"order" => 0,
	"href" => OCP\Util::linkTo("files", "index.php"),
	"icon" => OCP\Util::imagePath("core", "places/files.svg"),
	"name" => $l->t("Files")));

\OC::$server->getSearch()->registerProvider('OC\Search\Provider\File', array('apps' => array('files')));

$templateManager = OC_Helper::getFileTemplateManager();
$templateManager->registerTemplate('text/html', 'core/templates/filetemplates/template.html');
$templateManager->registerTemplate('application/vnd.oasis.opendocument.presentation', 'core/templates/filetemplates/template.odp');
$templateManager->registerTemplate('application/vnd.oasis.opendocument.text', 'core/templates/filetemplates/template.odt');
$templateManager->registerTemplate('application/vnd.oasis.opendocument.spreadsheet', 'core/templates/filetemplates/template.ods');

\OCA\Files\App::getNavigationManager()->add(
	array(
		"id" => 'files',
		"appname" => 'files',
		"script" => 'list.php',
		"order" => 0,
		"name" => $l->t('All files')
	)
);

\OC::$server->getActivityManager()->registerExtension(function() {
	return new \OCA\Files\Activity(
		\OC::$server->query('L10NFactory'),
		\OC::$server->getURLGenerator()
	);
});

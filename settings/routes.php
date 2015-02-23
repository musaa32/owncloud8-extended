<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Bjoern Schiessle <schiessle@owncloud.com>
 * @author Björn Schießle <schiessle@owncloud.com>
 * @author Christopher Schäpers <kondou@ts.unde.re>
 * @author Clark Tomlinson <fallen013@gmail.com>
 * @author Frank Karlitschek <frank@owncloud.org>
 * @author Georg Ehrke <georg@owncloud.com>
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Raghu Nayyar <me@iraghu.com>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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
namespace OC\Settings;

$application = new Application();
$application->registerRoutes($this, array(
	'resources' => array(
		'groups' => array('url' => '/settings/users/groups'),
		'users' => array('url' => '/settings/users/users')
	),
	'routes' => array(
		array('name' => 'MailSettings#setMailSettings', 'url' => '/settings/admin/mailsettings', 'verb' => 'POST'),
		array('name' => 'MailSettings#storeCredentials', 'url' => '/settings/admin/mailsettings/credentials', 'verb' => 'POST'),
		array('name' => 'MailSettings#sendTestMail', 'url' => '/settings/admin/mailtest', 'verb' => 'POST'),
		array('name' => 'AppSettings#listCategories', 'url' => '/settings/apps/categories', 'verb' => 'GET'),
		array('name' => 'AppSettings#listApps', 'url' => '/settings/apps/list', 'verb' => 'GET'),
		array('name' => 'SecuritySettings#enforceSSL', 'url' => '/settings/admin/security/ssl', 'verb' => 'POST'),
		array('name' => 'SecuritySettings#enforceSSLForSubdomains', 'url' => '/settings/admin/security/ssl/subdomains', 'verb' => 'POST'),
		array('name' => 'SecuritySettings#trustedDomains', 'url' => '/settings/admin/security/trustedDomains', 'verb' => 'POST'),
		array('name' => 'Users#setMailAddress', 'url' => '/settings/users/{id}/mailAddress', 'verb' => 'PUT'),
		array('name' => 'LogSettings#setLogLevel', 'url' => '/settings/admin/log/level', 'verb' => 'POST'),
		array('name' => 'LogSettings#getEntries', 'url' => '/settings/admin/log/entries', 'verb' => 'GET'),
		array('name' => 'LogSettings#download', 'url' => '/settings/admin/log/download', 'verb' => 'GET'),
	)
));

/** @var $this \OCP\Route\IRouter */

// Settings pages
$this->create('settings_help', '/settings/help')
	->actionInclude('settings/help.php');
$this->create('settings_personal', '/settings/personal')
	->actionInclude('settings/personal.php');
$this->create('settings_users', '/settings/users')
	->actionInclude('settings/users.php');
$this->create('settings_apps', '/settings/apps')
	->actionInclude('settings/apps.php');
$this->create('settings_admin', '/settings/admin')
	->actionInclude('settings/admin.php');
// Settings ajax actions
// users
$this->create('settings_ajax_everyonecount', '/settings/ajax/geteveryonecount')
	->actionInclude('settings/ajax/geteveryonecount.php');
$this->create('settings_ajax_setquota', '/settings/ajax/setquota.php')
	->actionInclude('settings/ajax/setquota.php');
$this->create('settings_ajax_togglegroups', '/settings/ajax/togglegroups.php')
	->actionInclude('settings/ajax/togglegroups.php');
$this->create('settings_ajax_togglesubadmins', '/settings/ajax/togglesubadmins.php')
	->actionInclude('settings/ajax/togglesubadmins.php');
$this->create('settings_users_changepassword', '/settings/users/changepassword')
	->post()
	->action('OC\Settings\ChangePassword\Controller', 'changeUserPassword');
$this->create('settings_ajax_changedisplayname', '/settings/ajax/changedisplayname.php')
	->actionInclude('settings/ajax/changedisplayname.php');
$this->create('settings_ajax_changegorupname', '/settings/ajax/changegroupname.php')
	->actionInclude('settings/ajax/changegroupname.php');	
// personal
$this->create('settings_personal_changepassword', '/settings/personal/changepassword')
	->post()
	->action('OC\Settings\ChangePassword\Controller', 'changePersonalPassword');
$this->create('settings_ajax_setlanguage', '/settings/ajax/setlanguage.php')
	->actionInclude('settings/ajax/setlanguage.php');
$this->create('settings_ajax_decryptall', '/settings/ajax/decryptall.php')
	->actionInclude('settings/ajax/decryptall.php');
$this->create('settings_ajax_restorekeys', '/settings/ajax/restorekeys.php')
	->actionInclude('settings/ajax/restorekeys.php');
$this->create('settings_ajax_deletekeys', '/settings/ajax/deletekeys.php')
	->actionInclude('settings/ajax/deletekeys.php');
$this->create('settings_cert_post', '/settings/ajax/addRootCertificate')
	->actionInclude('settings/ajax/addRootCertificate.php');
$this->create('settings_cert_remove', '/settings/ajax/removeRootCertificate')
	->actionInclude('settings/ajax/removeRootCertificate.php');
// apps
$this->create('settings_ajax_enableapp', '/settings/ajax/enableapp.php')
	->actionInclude('settings/ajax/enableapp.php');
$this->create('settings_ajax_disableapp', '/settings/ajax/disableapp.php')
	->actionInclude('settings/ajax/disableapp.php');
$this->create('settings_ajax_updateapp', '/settings/ajax/updateapp.php')
	->actionInclude('settings/ajax/updateapp.php');
$this->create('settings_ajax_uninstallapp', '/settings/ajax/uninstallapp.php')
	->actionInclude('settings/ajax/uninstallapp.php');
$this->create('settings_ajax_navigationdetect', '/settings/ajax/navigationdetect.php')
	->actionInclude('settings/ajax/navigationdetect.php');
// admin
$this->create('settings_ajax_excludegroups', '/settings/ajax/excludegroups.php')
	->actionInclude('settings/ajax/excludegroups.php');
$this->create('settings_ajax_checksetup', '/settings/ajax/checksetup')
	->actionInclude('settings/ajax/checksetup.php');

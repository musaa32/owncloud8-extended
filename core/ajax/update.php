<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Michael Gapczynski <gapczynskim@gmail.com>
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
set_time_limit(0);
require_once '../../lib/base.php';

if (OC::checkUpgrade(false)) {
	// if a user is currently logged in, their session must be ignored to
	// avoid side effects
	\OC_User::setIncognitoMode(true);

	$l = new \OC_L10N('core');
	$eventSource = \OC::$server->createEventSource();
	$updater = new \OC\Updater(
			\OC::$server->getHTTPHelper(),
			\OC::$server->getAppConfig(),
			\OC_Log::$object
	);
	$updater->listen('\OC\Updater', 'maintenanceStart', function () use ($eventSource, $l) {
		$eventSource->send('success', (string)$l->t('Turned on maintenance mode'));
	});
	$updater->listen('\OC\Updater', 'maintenanceEnd', function () use ($eventSource, $l) {
		$eventSource->send('success', (string)$l->t('Turned off maintenance mode'));
	});
	$updater->listen('\OC\Updater', 'dbUpgrade', function () use ($eventSource, $l) {
		$eventSource->send('success', (string)$l->t('Updated database'));
	});
	$updater->listen('\OC\Updater', 'dbSimulateUpgrade', function () use ($eventSource, $l) {
		$eventSource->send('success', (string)$l->t('Checked database schema update'));
	});
	$updater->listen('\OC\Updater', 'appUpgradeCheck', function () use ($eventSource, $l) {
		$eventSource->send('success', (string)$l->t('Checked database schema update for apps'));
	});
	$updater->listen('\OC\Updater', 'appUpgrade', function ($app, $version) use ($eventSource, $l) {
		$eventSource->send('success', (string)$l->t('Updated "%s" to %s', array($app, $version)));
	});
	$updater->listen('\OC\Updater', 'disabledApps', function ($appList) use ($eventSource, $l) {
		$list = array();
		foreach ($appList as $appId) {
			$info = OC_App::getAppInfo($appId);
			$list[] = $info['name'] . ' (' . $info['id'] . ')';
		}
		$eventSource->send('success', (string)$l->t('Disabled incompatible apps: %s', implode(', ', $list)));
	});
	$updater->listen('\OC\Updater', 'failure', function ($message) use ($eventSource) {
		$eventSource->send('failure', $message);
		$eventSource->close();
		OC_Config::setValue('maintenance', false);
	});

	$updater->upgrade();

	$eventSource->send('done', '');
	$eventSource->close();
}

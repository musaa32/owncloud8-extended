<?php
/**
 * @author Andreas Fischer <bantu@owncloud.com>
 * @author Arthur Schiwon <blizzz@owncloud.com>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Morris Jobke <hey@morrisjobke.de>
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
$repair = new \OC\Repair(\OC\Repair::getRepairSteps());

/** @var $application Symfony\Component\Console\Application */
$application->add(new OC\Core\Command\Status);
$application->add(new OC\Core\Command\Db\GenerateChangeScript());
$application->add(new OC\Core\Command\Db\ConvertType(\OC::$server->getConfig(), new \OC\DB\ConnectionFactory()));
$application->add(new OC\Core\Command\Upgrade(\OC::$server->getConfig()));
$application->add(new OC\Core\Command\Maintenance\SingleUser());
$application->add(new OC\Core\Command\Maintenance\Mode(\OC::$server->getConfig()));
$application->add(new OC\Core\Command\App\CheckCode());
$application->add(new OC\Core\Command\App\Disable());
$application->add(new OC\Core\Command\App\Enable());
$application->add(new OC\Core\Command\App\ListApps());
$application->add(new OC\Core\Command\Maintenance\Repair($repair, \OC::$server->getConfig()));
$application->add(new OC\Core\Command\User\Report());
$application->add(new OC\Core\Command\User\ResetPassword(\OC::$server->getUserManager()));
$application->add(new OC\Core\Command\User\LastSeen());
$application->add(new OC\Core\Command\User\Delete(\OC::$server->getUserManager()));
$application->add(new OC\Core\Command\L10n\CreateJs());


<?php
/**
 * @author Andreas Fischer <bantu@owncloud.com>
 * @author Christopher Schäpers <kondou@ts.unde.re>
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
namespace OC\Core\Command\User;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ResetPassword extends Command {

	/** @var \OC\User\Manager */
	protected $userManager;

	public function __construct(\OC\User\Manager $userManager) {
		$this->userManager = $userManager;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('user:resetpassword')
			->setDescription('Resets the password of the named user')
			->addArgument(
				'user',
				InputArgument::REQUIRED,
				'Username to reset password'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$username = $input->getArgument('user');

		/** @var $user \OC\User\User */
		$user = $this->userManager->get($username);
		if (is_null($user)) {
			$output->writeln("<error>There is no user called " . $username . "</error>");
			return 1;
		}

		if ($input->isInteractive()) {
			/** @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
			$dialog = $this->getHelperSet()->get('dialog');

			if (\OCP\App::isEnabled('files_encryption')) {
				$output->writeln(
					'<error>Warning: Resetting the password when using encryption will result in data loss!</error>'
				);
				if (!$dialog->askConfirmation($output, '<question>Do you want to continue?</question>', true)) {
					return 1;
				}
			}

			$password = $dialog->askHiddenResponse(
				$output,
				'<question>Enter a new password: </question>',
				false
			);
			$confirm = $dialog->askHiddenResponse(
				$output,
				'<question>Confirm the new password: </question>',
				false
			);

			if ($password === $confirm) {
				$success = $user->setPassword($password);
				if ($success) {
					$output->writeln("<info>Successfully reset password for " . $username . "</info>");
				} else {
					$output->writeln("<error>Error while resetting password!</error>");
					return 1;
				}
			} else {
				$output->writeln("<error>Passwords did not match!</error>");
				return 1;
			}
		} else {
			$output->writeln("<error>Interactive input is needed for entering a new password!</error>");
			return 1;
		}
	}
}

<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
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


namespace OCA\Notifications\Command;


use OCA\Notifications\AppInfo\Application;
use OCP\IGroup;
use OCP\IURLGenerator;
use OCP\Notification\IManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Command {

	/** @var IManager */
	private $manager;
	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * @param IManager $manager
	 */
	function __construct(IManager $manager, IURLGenerator $urlGenerator) {
		parent::__construct();
		$this->manager = $manager;
		$this->urlGenerator = $urlGenerator;
	}

	protected function configure() {
		$this
			->setName('notifications:generate')
			->setDescription('Generates a notification')
			->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'User id to whom the notification shall be sent to')
			->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Group id to whom the notification shall be sent to')
			->addArgument('subject', InputArgument::REQUIRED, 'The notification subject - maximum 255 characters')
			->addArgument('message', InputArgument::OPTIONAL, 'A longer message - maximum 4000 characters')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$user = $input->getOption('user');
		$group = $input->getOption('group');
		if ($user === null && $group === null) {
			throw new \Exception('Either user or group needs to be given.');
		}
		$subject = $input->getArgument('subject');
		$message = $input->getArgument('message');

		$users = [$user];
		if ($group !== null) {
			$group = \OC::$server->getGroupManager()->get($group);
			if ($group === null) {
				throw new \Exception('Group is not known.');
			}
			$users = array_map(function($g) {
				/** @var IGroup $g */
				return $g->getGID();
			}, $group->getUsers());
		}

		foreach($users as $user) {
			$time = time();
			$datetime = new \DateTime();
			$datetime->setTimestamp($time);

			$notification = $this->manager->createNotification();
			$notification->setApp('notifications');
			$notification->setDateTime($datetime);
			if ($message !== null) {
				$notification->setMessage('admin-notification', [$message]);
			}
			$notification->setSubject('admin-notification', [$subject]);
			$notification->setObject('admin-notification', $time);
			if (method_exists($notification, 'setIcon')) {
				$notification->setIcon($this->urlGenerator->imagePath('notifications', 'icon.png'));
			}

			$notification->setUser($user);
			$this->manager->notify($notification);
		}
	}
}
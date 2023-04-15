<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2023, Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\AdminTrainingGame\Migration;

use OCA\AdminTrainingGame\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\Notification\IManager;

class CreateInitializeGame implements IRepairStep {
	public function __construct(
		protected IConfig $config,
		protected IGroupManager $groupManager,
		protected IManager $notificationManager,
		protected ITimeFactory $timeFactory,
	) {
	}

	public function getName(): string {
		return 'Create initial notification for the Admin Training Game';
	}

	public function run(IOutput $output): void {
		$adminGroup = $this->groupManager->get('admin');

		if (!$adminGroup instanceof IGroup) {
			throw new \RuntimeException('Admin group not found');
		}

		$notification = $this->notificationManager->createNotification();
		$notification->setApp(Application::APP_ID)
			->setSubject('state')
			->setObject('game', '1')
			->setDateTime($this->timeFactory->getDateTime());

		foreach ($adminGroup->getUsers() as $adminUser) {
			$this->config->setUserValue($adminUser->getUID(), Application::APP_ID, 'game_1_level', '0');
			$notification->setUser($adminUser->getUID());
			$this->notificationManager->notify($notification);
		}
	}
}

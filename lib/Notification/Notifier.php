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

namespace OCA\AdminTrainingGame\Notification;

use OCA\AdminTrainingGame\AppInfo\Application;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	public function __construct(
		protected IConfig $config,
		protected IFactory $l10nFactory,
		protected IURLGenerator $urlGenerator,
	) {
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10nFactory->get(Application::APP_ID)->t('Admin Training Game');
	}

	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== Application::APP_ID) {
			throw new \InvalidArgumentException('Unknown app');
		}

		if ($notification->getObjectType() !== 'game') {
			throw new \InvalidArgumentException('Unknown notification');
		}

		if ($notification->getObjectId() !== '1') {
			throw new \InvalidArgumentException('Unknown game');
		}

		$level = (int) $this->config->getUserValue($notification->getUser(), Application::APP_ID, 'game_1_level', '0');

		$l = $this->l10nFactory->get(Application::APP_ID, $languageCode);
		$notification->setParsedSubject($l->t('Admin Training Game: Level %s', $level));
		$notification->setIcon(
			$this->urlGenerator->getAbsoluteURL(
				$this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg')
			)
		);

		if ($level === 0) {
			$notification->setParsedSubject($l->t('Welcome to the "Admin Training Game"!'));
			$notification->setParsedMessage($l->t('Shall we play a game?'));
		} else {
			throw new \InvalidArgumentException('Unknown level');
		}

		return $notification;
	}
}

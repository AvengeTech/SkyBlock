<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level7;

use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\spawners\event\SpawnerKillEvent;
use skyblock\spawners\event\SpawnerUpgradeEvent;

class UpgradeMobSpawner3Challenge extends Challenge{
	
	public function onSpawnerEvent(SpawnerKillEvent|SpawnerUpgradeEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof SpawnerUpgradeEvent ||
			$event->getNewLevel() !== 10
		) return false;

		$this->onCompleted($player);
		return true;
	}
}
<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level4;

use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\spawners\event\SpawnerKillEvent;
use skyblock\spawners\event\SpawnerUpgradeEvent;

class UpgradeMobSpawner1Challenge extends Challenge{
	
	public function onSpawnerEvent(SpawnerKillEvent|SpawnerUpgradeEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof SpawnerUpgradeEvent ||
			$event->getNewLevel() !== 2
		) return false;

		$this->onCompleted($player);
		return true;
	}
}
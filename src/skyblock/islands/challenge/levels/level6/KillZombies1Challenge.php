<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level6;

use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\spawners\entity\hostile\Zombie;
use skyblock\spawners\event\SpawnerKillEvent;
use skyblock\spawners\event\SpawnerUpgradeEvent;

class KillZombies1Challenge extends Challenge{
	
	public function onSpawnerEvent(SpawnerKillEvent|SpawnerUpgradeEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof SpawnerKillEvent ||
			!$event->getMob() instanceof Zombie
		) return false;

		$this->progress["killed"]["progress"]++;

		if($this->progress["killed"]["progress"] < $this->progress["killed"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}
}
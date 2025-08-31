<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level14;

use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\spawners\entity\passive\IronGolem;
use skyblock\spawners\event\SpawnerKillEvent;
use skyblock\spawners\event\SpawnerUpgradeEvent;

class KillGolem1Challenge extends Challenge{
	
	public function onSpawnerEvent(SpawnerKillEvent|SpawnerUpgradeEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof SpawnerKillEvent ||
			!$event->getMob() instanceof IronGolem
		) return false;

		$this->progress["killed"]["progress"]++;

		if($this->progress["killed"]["progress"] < $this->progress["killed"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}
}
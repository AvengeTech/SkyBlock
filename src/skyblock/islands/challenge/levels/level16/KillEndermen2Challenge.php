<?php namespace skyblock\islands\challenge\levels\level16;

use pocketmine\event\Event;
use pocketmine\player\Player;

use skyblock\islands\challenge\Challenge;
use skyblock\spawners\event\SpawnerKillEvent;
use skyblock\spawners\event\SpawnerUpgradeEvent;

class KillEndermen2Challenge extends Challenge{

	public function onSpawnerEvent(SpawnerKillEvent|SpawnerUpgradeEvent $event, Player $player): bool
	{
		if(!$this->isCompleted()){
			$mob = $event->getMob();
			if($mob->getName() == "Enderman"){
				$this->progress["endermen"]["progress"]++;
				if($this->progress["endermen"]["progress"] >= 200){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}
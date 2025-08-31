<?php namespace skyblock\islands\challenge\levels\level19;

use pocketmine\event\Event;
use pocketmine\player\Player;
use skyblock\generators\event\GeneratorEvent;
use skyblock\generators\event\GeneratorUpgradeEvent;
use skyblock\generators\tile\OreGenerator;
use skyblock\islands\challenge\Challenge;

class UpgradeGlowingObsidianGen2Challenge extends Challenge{

	public function onGeneratorEvent(GeneratorEvent $event, Player $player): bool
	{
		/** @var GeneratorUpgradeEvent $event */
		if(!$this->isCompleted()){
			$gen = $event->getGenerator();
			if($gen instanceof OreGenerator){
				$type = $gen->getType();
				if($type == 8){ //glowing obsidian
					$level = $event->getNewLevel();
					if($level == 8){
						$this->onCompleted($player);
						return true;
					}
				}
			}
		}
		return false;
	}

}
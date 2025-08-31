<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level12;

use pocketmine\player\Player;
use skyblock\generators\event\GeneratorEvent;
use skyblock\generators\tile\OreGenerator;
use skyblock\islands\challenge\Challenge;
use skyblock\generators\event\GeneratorUpgradeEvent;

class UpgradeObsidianGenerator1Challenge extends Challenge{

	public function onGeneratorEvent(GeneratorEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() || 
			!$event instanceof GeneratorUpgradeEvent
		) return false;

		$generator = $event->getGenerator();

		if(
			!$generator instanceof OreGenerator ||
			$generator->getType() !== OreGenerator::TYPE_OBSIDIAN ||
			$event->getNewLevel() !== 5
		) return false;

		$this->onCompleted($player);
		return true;
	}

}
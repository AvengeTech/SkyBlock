<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level1;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\player\Player;

use skyblock\islands\challenge\Challenge;

class CraftBedChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaBlocks::BED()->asItem(), false, false)) continue;

			$this->onCompleted($player);
			return true;
		}

		return false;
	}

}
<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level1;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\CraftItemEvent;
use skyblock\islands\challenge\Challenge;

class CraftFurnaceChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaBlocks::FURNACE()->asItem(), false, false)) continue;

			$this->onCompleted($player);
			return true;
		}

		return false;
	}

}
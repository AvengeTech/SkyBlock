<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level2;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\CraftItemEvent;

use skyblock\islands\challenge\Challenge;

class CraftBricksChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaBlocks::BRICKS()->asItem(), false, false)) continue;

			$this->progress["crafted"]["progress"] += $output->getCount();

			if($this->progress["crafted"]["progress"] < $this->progress["crafted"]["needed"]) continue;

			$this->progress["crafted"]["progress"] = $this->progress["crafted"]["needed"];
			$this->onCompleted($player);
			return true;
		}

		return false;
	}

}
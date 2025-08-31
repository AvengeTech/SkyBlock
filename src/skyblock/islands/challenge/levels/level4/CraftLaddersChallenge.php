<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level4;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\player\Player;

use skyblock\islands\challenge\Challenge;

class CraftLaddersChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaBlocks::LADDER()->asItem(), false, false)) continue;

			$this->progress["crafted"]["progress"] += $output->getCount();

			if($this->progress["crafted"]["progress"] < $this->progress["crafted"]["needed"]) continue;

			$this->progress["crafted"]["progress"] = $this->progress["crafted"]["needed"];
			$this->onCompleted($player);
			return true;
		}

		return false;
	}

}
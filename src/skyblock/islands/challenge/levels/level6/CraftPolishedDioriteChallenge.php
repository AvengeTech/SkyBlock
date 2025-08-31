<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level6;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use skyblock\islands\challenge\Challenge;

class CraftPolishedDioriteChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaBlocks::POLISHED_DIORITE()->asItem(), false, false)) return false;

			$this->progress["crafted"]["progress"] += $output->getCount();

			if($this->progress["crafted"]["progress"] < $this->progress["crafted"]["needed"]) return false;

			$this->progress["crafted"]["progress"] = $this->progress["crafted"]["needed"];
			$this->onCompleted($player);
			return true;
		}

		return false;
	}

}
<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level5;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use skyblock\islands\challenge\Challenge;

class CraftPolishedGraniteChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaBlocks::POLISHED_GRANITE()->asItem(), false, false)) return false;

			$this->progress["crafted"]["progress"] += $output->getCount();

			if($this->progress["crafted"]["progress"] < $this->progress["crafted"]["needed"]) return false;

			$this->progress["crafted"]["progress"] = $this->progress["crafted"]["needed"];
			$this->onCompleted($player);
			return true;
		}

		return false;
	}

}
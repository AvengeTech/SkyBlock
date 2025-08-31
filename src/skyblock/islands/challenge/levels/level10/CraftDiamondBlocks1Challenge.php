<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level10;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\CraftItemEvent;
use skyblock\islands\challenge\Challenge;

class CraftDiamondBlocks1Challenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaBlocks::DIAMOND()->asItem(), false, false)) return false;

			$this->progress["crafted"]["progress"] += $output->getCount();

			if($this->progress["crafted"]["progress"] < $this->progress["crafted"]["needed"]) return false;

			$this->progress["crafted"]["progress"] = $this->progress["crafted"]["needed"];
			$this->onCompleted($player);
			return true;
		}

		return false;
	}

}
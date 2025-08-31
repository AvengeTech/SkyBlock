<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level2;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\CraftItemEvent;
use skyblock\islands\challenge\Challenge;

class CraftCobblestoneOakStairChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if($output->equals(VanillaBlocks::COBBLESTONE_STAIRS()->asItem(), false, false)){
				$this->progress["cobblestone"]["progress"] += $output->getCount();

				if($this->progress["cobblestone"]["progress"] < $this->progress["cobblestone"]["needed"]) continue;

				$this->progress["cobblestone"]["progress"] = $this->progress["cobblestone"]["needed"];
			}

			if($output->equals(VanillaBlocks::OAK_STAIRS()->asItem(), false, false)){
				$this->progress["wood"]["progress"] += $output->getCount();

				if($this->progress["wood"]["progress"] < $this->progress["wood"]["needed"]) continue;

				$this->progress["wood"]["progress"] = $this->progress["wood"]["needed"];
			}
		}

		if(
			$this->progress["cobblestone"]["progress"] < $this->progress["cobblestone"]["needed"] &&
			$this->progress["wood"]["progress"] < $this->progress["wood"]["needed"]
		) return false;

		$this->onCompleted($player);
		return true;
	}

}
<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level1;

use pocketmine\player\Player;
use pocketmine\block\Trapdoor;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\ItemBlock;
use skyblock\islands\challenge\Challenge;

class CraftTrapdoorChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!($output instanceof ItemBlock && $output->getBlock() instanceof Trapdoor)) continue;

			$this->progress["crafted"]["progress"] += $output->getCount();

			if($this->progress["crafted"]["progress"] < $this->progress["crafted"]["needed"]) continue;

			$this->progress["crafted"]["progress"] = $this->progress["crafted"]["needed"];
			$this->onCompleted($player);
			return true;
		}

		return false;
	}

}
<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level3;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use skyblock\islands\challenge\Challenge;

class CraftIronNuggetsChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaItems::IRON_NUGGET(), false, false)) continue;

			$this->progress["crafted"]["progress"] += $output->getCount();

			if($this->progress["crafted"]["progress"] < $this->progress["crafted"]["needed"]) continue;

			$this->progress["crafted"]["progress"] = $this->progress["crafted"]["needed"];
			$this->onCompleted($player);
			return true;
		}
		return false;
	}

}
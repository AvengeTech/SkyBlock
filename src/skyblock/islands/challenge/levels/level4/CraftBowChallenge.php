<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level4;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;

use skyblock\islands\challenge\Challenge;

class CraftBowChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaItems::BOW(), false, false)) continue;

			$this->onCompleted($player);
			return true;
		}

		return false;
	}
}
<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level13;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use skyblock\islands\challenge\Challenge;

class CraftClockChallenge extends Challenge{

	public function onCraftEvent(CraftItemEvent $event, Player $player): bool{
		if($this->isCompleted()) return false;

		foreach($event->getOutputs() as $output){
			if(!$output->equals(VanillaItems::CLOCK())) continue;

			$this->onCompleted($player);
			return true;
		}

		return false;
	}
}
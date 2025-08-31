<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level6;

use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\pets\event\PetEvent;
use skyblock\pets\event\PetGainXpEvent;
use skyblock\pets\event\UnlockPetBoxEvent;

class GainPetXp1Challenge extends Challenge{

	public function onPetEvent(PetEvent|UnlockPetBoxEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() || 
			!$event instanceof PetGainXpEvent
		) return false;

		$this->progress["gained"]["progress"] += $event->getXp();

		if($this->progress["gained"]["progress"] < $this->progress["gained"]["needed"]) return false;

		$this->progress["gained"]["progress"] = $this->progress["gained"]["needed"];
		$this->onCompleted($player);
		return true;
	}
}
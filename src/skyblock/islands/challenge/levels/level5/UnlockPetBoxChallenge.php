<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level5;

use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\pets\event\PetEvent;
use skyblock\pets\event\UnlockPetBoxEvent;

class UnlockPetBoxChallenge extends Challenge{

	public function onPetEvent(PetEvent|UnlockPetBoxEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() || 
			!$event instanceof UnlockPetBoxEvent
		) return false;

		$this->onCompleted($player);
		return true;
	}
}
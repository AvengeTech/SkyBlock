<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level7;

use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\pets\event\PetEvent;
use skyblock\pets\event\PetLevelUpEvent;
use skyblock\pets\event\UnlockPetBoxEvent;

class PetLevelUp1Challenge extends Challenge{

	public function onPetEvent(PetEvent|UnlockPetBoxEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof PetLevelUpEvent ||
			$event->getLevel() < 2
		) return false;

		$this->onCompleted($player);
		return true;
	}
}
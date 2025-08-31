<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level9;

use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\pets\event\PetEvent;
use skyblock\pets\event\PetLevelUpEvent;
use skyblock\pets\event\UnlockPetBoxEvent;

class PetLevelUp2Challenge extends Challenge{

	public function onPetEvent(PetEvent|UnlockPetBoxEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof PetLevelUpEvent ||
			$event->getLevel() < 3
		) return false;

		$this->onCompleted($player);
		return true;
	}
}
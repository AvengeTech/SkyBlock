<?php

namespace skyblock\pets\event;

use pocketmine\event\Event;
use pocketmine\player\Player;
use skyblock\pets\block\PetBox;
use skyblock\pets\item\PetEgg;

class UnlockPetBoxEvent extends Event{
	
	public function __construct(
		private Player $player,
		private PetBox $box,
		private PetEgg $egg
	){}

	public function getPlayer() : Player{ return $this->player; }

	public function getBox() : PetBox{ return $this->box; }

	public function getEgg() : PetEgg{ return $this->egg; }
}
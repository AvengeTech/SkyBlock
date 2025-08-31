<?php

namespace skyblock\pets\event;

use pocketmine\event\Event;
use pocketmine\player\Player;
use skyblock\pets\types\PetData;

class PetEvent extends Event{
	
	public function __construct(
		private Player $player, 
		private PetData $pet
	){}

	public function getPlayer() : Player{ return $this->player; }

	public function getPet() : PetData{ return $this->pet; }
}
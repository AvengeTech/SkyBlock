<?php

namespace skyblock\pets\event;

use pocketmine\player\Player;
use skyblock\pets\types\PetData;

class PetGainXpEvent extends PetEvent{
	
	public function __construct(
		Player $player, 
		PetData $pet,
		private int $xp
	){
		parent::__construct($player, $pet);
	}

	public function getXp() : int{ return $this->xp; }
}
<?php

namespace skyblock\pets\event;

use pocketmine\player\Player;
use skyblock\pets\types\PetData;

class PetLevelUpEvent extends PetEvent{
	
	public function __construct(
		Player $player, 
		PetData $pet,
		private int $oldLevel,
		private int $level
	){
		parent::__construct($player, $pet);
	}

	public function getOldLevel() : int{ return $this->oldLevel; }

	public function getLevel() : int{ return $this->level; }
}
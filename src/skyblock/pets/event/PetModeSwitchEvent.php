<?php

namespace skyblock\pets\event;

use pocketmine\player\Player;
use skyblock\pets\types\PetData;

class PetModeSwitchEvent extends PetEvent{
	
	public function __construct(
		Player $player, 
		PetData $pet
	){
		parent::__construct($player, $pet);
	}
}
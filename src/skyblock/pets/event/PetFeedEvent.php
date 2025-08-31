<?php

namespace skyblock\pets\event;

use pocketmine\player\Player;
use skyblock\pets\item\PetFeed;
use skyblock\pets\types\PetData;

class PetFeedEvent extends PetEvent{
	
	public function __construct(
		Player $player, 
		PetData $pet,
		private PetFeed $feed
	){
		parent::__construct($player, $pet);
	}

	public function getFeed() : PetFeed{ return $this->feed; }
}
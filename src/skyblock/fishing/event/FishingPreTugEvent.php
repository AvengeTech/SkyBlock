<?php

namespace skyblock\fishing\event;

use pocketmine\player\Player;

use skyblock\fishing\item\FishingRod;

class FishingPreTugEvent extends FishingEvent{

	public function __construct(
		Player $player,
		FishingRod $rod,
		private int $tugTime
	){
		parent::__construct($player, $rod);
	}

	public function getNextTug() : int{ return $this->tugTime; }

	public function setNextTug(int $tugTime) : self{
		$this->tugTime = $tugTime;

		return $this;
	}
}
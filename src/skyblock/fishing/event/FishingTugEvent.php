<?php

namespace skyblock\fishing\event;

use pocketmine\player\Player;

use skyblock\fishing\item\FishingRod;

class FishingTugEvent extends FishingEvent{

	public function __construct(
		Player $player,
		FishingRod $rod,
		private int $tugTime
	){
		parent::__construct($player, $rod);
	}

	public function getTugTime() : int{ return $this->tugTime; }

	public function setTugTime(int $tugTime) : self{
		$this->tugTime = $tugTime;

		return $this;
	}
}
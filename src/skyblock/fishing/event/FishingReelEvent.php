<?php

namespace skyblock\fishing\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;
use skyblock\fishing\item\FishingRod;

class FishingReelEvent extends FishingEvent implements Cancellable{
	
	use CancellableTrait;

	public function __construct(
		Player $player,
		FishingRod $rod,
		private array $extraData = []
	){
		parent::__construct($player, $rod);
	}

	public function getExtraData() : array{ return $this->extraData; }

	public function setExtraData(array $data) : self{
		$this->extraData = $data;

		return $this;
	}
}
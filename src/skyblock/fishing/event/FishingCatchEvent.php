<?php namespace skyblock\fishing\event;

use pocketmine\player\Player;
use skyblock\fishing\entity\Hook;
use skyblock\fishing\item\FishingRod;
use skyblock\fishing\object\FishingFind;

class FishingCatchEvent extends FishingEvent{

	public function __construct(
		Player $player,
		FishingRod $rod,
		private FishingFind $find,
		private int $liquidType
	){
		parent::__construct($player, $rod);
	}

	public function getFind() : FishingFind{return $this->find; }

	public function getLiquidType() : int{ return $this->liquidType; }

	public function inWater() : bool{ return $this->liquidType === Hook::LIQUID_WATER; }

	public function inLava() : bool{ return $this->liquidType === Hook::LIQUID_LAVA; }
}
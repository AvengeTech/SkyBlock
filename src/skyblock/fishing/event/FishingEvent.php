<?php namespace skyblock\fishing\event;

use pocketmine\event\Event;
use pocketmine\player\Player;
use skyblock\fishing\item\FishingRod;

class FishingEvent extends Event{

	public function __construct(
		private Player $player,
		private FishingRod $rod
	){}

	public function getPlayer() : Player{ return $this->player; }

	public function getFishingRod() : FishingRod{ return $this->rod; }

}
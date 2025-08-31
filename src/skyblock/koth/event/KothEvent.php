<?php namespace skyblock\koth\event;

use pocketmine\event\Event;

use skyblock\koth\pieces\Game;

class KothEvent extends Event{

	public function __construct(
		private Game $game
	){}

	public function getGame() : Game{
		return $this->game;
	}

}
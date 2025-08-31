<?php namespace skyblock\lms\event;

use pocketmine\event\Event;

use skyblock\lms\Game;

class LmsEvent extends Event{

	public $game;

	public function __construct(Game $game){
		$this->game = $game;
	}

	public function getGame() : Game{
		return $this->game;
	}

}
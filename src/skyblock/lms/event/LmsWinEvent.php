<?php namespace skyblock\lms\event;

use pocketmine\player\Player;

use skyblock\lms\Game;

class LmsWinEvent extends LmsEvent{

	public $player;

	public function __construct(Game $game, Player $player){
		parent::__construct($game);
		$this->player = $player;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

}
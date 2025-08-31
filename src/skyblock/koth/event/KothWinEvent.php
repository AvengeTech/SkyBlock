<?php namespace skyblock\koth\event;

use pocketmine\player\Player;

use skyblock\koth\pieces\Game;

class KothWinEvent extends KothEvent{

	public function __construct(
		Game $game, 
		private Player $player
	){
		parent::__construct($game);
	}

	public function getPlayer() : Player{ return $this->player; }
}
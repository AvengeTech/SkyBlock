<?php namespace skyblock\crates\event;

use pocketmine\player\Player;

use skyblock\crates\entity\Crate;

class CratePlayerEvent extends CrateEvent{

	public function __construct(Crate $crate, public Player $player){
		parent::__construct($crate);
	}

	public function getPlayer() : Player{
		return $this->player;
	}

}
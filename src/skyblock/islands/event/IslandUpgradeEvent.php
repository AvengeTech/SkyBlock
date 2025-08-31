<?php namespace skyblock\islands\event;

use pocketmine\player\Player;

use skyblock\islands\Island;

class IslandUpgradeEvent extends IslandEvent{

	public function __construct(Island $island, public Player $player, public int $newlevel){
		parent::__construct($island);
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getNewLevel() : int{
		return $this->newlevel;
	}

}
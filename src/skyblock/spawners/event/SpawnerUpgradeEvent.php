<?php namespace skyblock\spawners\event;

use pocketmine\event\Event;
use pocketmine\player\Player;

class SpawnerUpgradeEvent extends Event{
	
	public function __construct(
		public Player $player,
		public int $oldlevel,
		public int $newlevel
	){}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getOldLevel() : int{
		return $this->oldlevel;
	}

	public function getNewLevel() : int{
		return $this->newlevel;
	}

	public function setNewLevel(int $newlevel) : void{
		$this->newlevel = $newlevel;
	}

}
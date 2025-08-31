<?php namespace skyblock\spawners\event;

use pocketmine\event\Event;
use pocketmine\player\Player;

use skyblock\spawners\entity\Mob;

class SpawnerKillEvent extends Event{

	public function __construct(
		public Mob $mob,
		public Player $player
	){}

	public function getMob() : Mob{
		return $this->mob;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

}
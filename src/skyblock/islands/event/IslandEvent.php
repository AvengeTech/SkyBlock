<?php namespace skyblock\islands\event;

use pocketmine\event\Event;

use skyblock\islands\Island;

class IslandEvent extends Event{
	
	public function __construct(public Island $island){}

	public function getIsland() : Island{
		return $this->island;
	}

}
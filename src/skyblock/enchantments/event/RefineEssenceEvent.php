<?php

namespace skyblock\enchantments\event;

use pocketmine\event\Event;
use pocketmine\player\Player;
use skyblock\item\Essence;

class RefineEssenceEvent extends Event{

	public function __construct(
		private Player $player,
		private Essence $essence
	){}

	public function getPlayer() : Player{ return $this->player; }

	public function getEssence() : Essence{ return $this->essence; }
}
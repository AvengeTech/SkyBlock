<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level13;

use pocketmine\player\Player;
use skyblock\enchantments\event\RefineEssenceEvent;
use skyblock\islands\challenge\Challenge;

class RefineEssence3Challenge extends Challenge{
	
	public function onEssenceEvent(RefineEssenceEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$this->progress["refined"]["progress"]++;

		if($this->progress["refined"]["progress"] < $this->progress["refined"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}
}
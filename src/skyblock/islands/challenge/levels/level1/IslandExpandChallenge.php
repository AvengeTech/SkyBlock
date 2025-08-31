<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level1;

use pocketmine\player\Player;

use skyblock\islands\challenge\Challenge;
use skyblock\islands\event\IslandEvent;

class IslandExpandChallenge extends Challenge{

	public function onIslandEvent(IslandEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$this->onCompleted($player);
		return true;
	}

}
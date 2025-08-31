<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level11;

use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\player\Player;

use skyblock\islands\challenge\Challenge;

class LevelUpChallenge extends Challenge{

	public function onExperienceEvent(PlayerExperienceChangeEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$newlevel = $event->getNewLevel();

		if(
			is_null($newlevel) || 
			$newlevel < $this->progress["level"]["progress"]
		) return false;

		$this->progress["level"]["progress"] = $newlevel;

		if($newlevel < $this->progress["level"]["needed"]) return false;

		$this->progress["level"]["progress"] = $this->progress["level"]["needed"];
		$this->onCompleted($player);
		return true;
	}

}
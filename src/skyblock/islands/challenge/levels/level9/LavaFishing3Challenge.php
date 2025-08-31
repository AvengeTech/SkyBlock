<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level9;

use pocketmine\player\Player;
use skyblock\fishing\event\FishingCatchEvent;
use skyblock\fishing\event\FishingEvent;
use skyblock\islands\challenge\Challenge;

class LavaFishing3Challenge extends Challenge{

	public function onFishEvent(FishingEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() || 
			!$event instanceof FishingCatchEvent || 
			$event->inWater()
		) return false;

		$item = $event->getFind()->getItem();

		$this->progress["collected"]["progress"] += $item->getCount();

		if($this->progress["collected"]["progress"] < $this->progress["collected"]["needed"]) return false;

		$this->progress["collected"]["progress"] = $this->progress["collected"]["needed"];
		$this->onCompleted($player);
		return true;
	}

}
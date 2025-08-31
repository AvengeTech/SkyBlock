<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level5;

use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use skyblock\fishing\event\FishingCatchEvent;
use skyblock\fishing\event\FishingEvent;
use skyblock\islands\challenge\Challenge;

class CollectFish3Challenge extends Challenge{

	public function onFishEvent(FishingEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof FishingCatchEvent ||
			$event->inLava()
		) return false;

		$item = $event->getFind()->getItem();

		if(!(
			$item->equals(VanillaItems::RAW_FISH(), false, false) ||
			$item->equals(VanillaItems::RAW_SALMON(), false, false) ||
			$item->equals(VanillaItems::CLOWNFISH(), false, false) ||
			$item->equals(VanillaItems::PUFFERFISH(), false, false)
		)) return false;

		$this->progress["collected"]["progress"] += $item->getCount();

		if($this->progress["collected"]["progress"] < $this->progress["collected"]["needed"]) return false;

		$this->progress["collected"]["progress"] = $this->progress["collected"]["needed"];
		$this->onCompleted($player);
		return true;
	}

}
<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level11;

use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use skyblock\event\AutoInventoryCollectEvent;
use skyblock\islands\challenge\Challenge;

class CollectRottenFleshChallenge extends Challenge{

	public function onCollectEvent(EntityItemPickupEvent|AutoInventoryCollectEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaItems::ROTTEN_FLESH(), false, false)) return false;

		$this->progress["collected"]["progress"] += $item->getCount();

		if($this->progress["collected"]["progress"] < $this->progress["collected"]["needed"]) return false;
	
		$this->progress["collected"]["progress"] = $this->progress["collected"]["needed"];
		$this->onCompleted($player);
		return true;
	}

}
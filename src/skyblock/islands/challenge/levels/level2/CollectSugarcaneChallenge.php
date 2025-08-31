<?php

namespace skyblock\islands\challenge\levels\level2;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityItemPickupEvent;
use skyblock\event\AutoInventoryCollectEvent;
use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;

class CollectSugarcaneChallenge extends Challenge{

	public function onCollectEvent(EntityItemPickupEvent|AutoInventoryCollectEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaBlocks::SUGARCANE()->asItem(), false, false)) return false;

		$this->progress["collected"]["progress"]++;

		if($this->progress["collected"]["progress"] < $this->progress["collected"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}
}
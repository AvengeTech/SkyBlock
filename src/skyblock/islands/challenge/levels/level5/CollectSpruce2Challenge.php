<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level5;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\player\Player;
use skyblock\event\AutoInventoryCollectEvent;
use skyblock\islands\challenge\Challenge;

class CollectSpruce2Challenge extends Challenge{

	public function onCollectEvent(EntityItemPickupEvent|AutoInventoryCollectEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaBlocks::SPRUCE_LOG()->asItem(), false, false)) return false;

		$this->progress["collected"]["progress"] += $item->getCount();

		if($this->progress["collected"]["progress"] < $this->progress["collected"]["needed"]) return false;

		$this->progress["collected"]["progress"] = $this->progress["collected"]["needed"];
		$this->onCompleted($player);
		return true;
	}

}
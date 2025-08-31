<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level12;

use core\utils\BlockRegistry;
use pocketmine\player\Player;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use skyblock\islands\challenge\Challenge;

class PlantBrownMushroomChallenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() || 
			!$event instanceof BlockPlaceEvent
		) return false;

		$item = $event->getItem();

		if(!$item->equals(BlockRegistry::BROWN_MUSHROOM()->asItem(), false, false)) return false;

		$this->progress["planted"]["progress"]++;

		if($this->progress["planted"]["progress"] < $this->progress["planted"]["needed"]) return false;
		
		$this->onCompleted($player);
		return true;
	}
}
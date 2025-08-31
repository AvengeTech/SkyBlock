<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level1;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use skyblock\islands\challenge\Challenge;

class PlantSugarcaneChallenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player): bool{
		if(
			$this->isCompleted() ||
			!$event instanceof BlockPlaceEvent
		) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaBlocks::SUGARCANE()->asItem(), false, false)) return false;

		$this->progress["planted"]["progress"]++;

		if($this->progress["planted"]["progress"] < $this->progress["planted"]["needed"]) return false;
		
		$this->onCompleted($player);
		return true;
	}

}
<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level9;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use skyblock\islands\challenge\Challenge;

class PlantDarkOakSaplingsChallenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() || 
			!$event instanceof BlockPlaceEvent
		) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaBlocks::DARK_OAK_SAPLING()->asItem(), false, false)) return false;

		$this->progress["planted"]["progress"]++;

		if($this->progress["planted"]["progress"] < $this->progress["planted"]["needed"]) return false;
		
		$this->onCompleted($player);
		return true;
	}
}
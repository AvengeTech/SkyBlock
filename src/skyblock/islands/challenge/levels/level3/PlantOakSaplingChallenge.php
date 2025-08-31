<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level3;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\player\Player;

use skyblock\islands\challenge\Challenge;

class PlantOakSaplingChallenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof BlockPlaceEvent
		) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaBlocks::OAK_SAPLING()->asItem(), false, false)) return false;

		$this->progress["planted"]["progress"]++;

		if($this->progress["planted"]["progress"] < 10) return false;

		$this->onCompleted($player);
		return true;
	}

}
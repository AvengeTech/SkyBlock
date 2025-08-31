<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level3;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use skyblock\islands\challenge\Challenge;

class PlantWheatChallenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof BlockPlaceEvent
		) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaItems::WHEAT_SEEDS(), false, false)) return false;

		$this->progress["planted"]["progress"]++;

		if($this->progress["planted"]["progress"] < $this->progress["planted"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}

}
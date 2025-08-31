<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level7;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;

class PlantPumpkinsChallenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof BlockPlaceEvent
		) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaItems::PUMPKIN_SEEDS(), false, false)) return false;

		$this->progress["planted"]["progress"]++;

		if($this->progress["planted"]["progress"] < $this->progress["planted"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}

}
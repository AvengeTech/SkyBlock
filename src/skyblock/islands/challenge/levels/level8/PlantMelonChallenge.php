<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level8;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\player\Player;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\item\VanillaItems;

use skyblock\islands\challenge\Challenge;

class PlantMelonChallenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player): bool{
		if(
			$this->isCompleted() ||
			!$event instanceof BlockPlaceEvent
		) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaItems::MELON_SEEDS(), false, false)) return false;

		$this->progress["planted"]["progress"]++;

		if($this->progress["planted"]["progress"] < $this->progress["planted"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}

}
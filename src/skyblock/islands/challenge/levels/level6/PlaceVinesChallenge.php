<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level6;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;

class PlaceVinesChallenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof BlockPlaceEvent
		) return false;

		$item = $event->getItem();

		if(!$item->equals(VanillaBlocks::VINES()->asItem(), false, false)) return false;

		$this->progress["placed"]["progress"]++;

		if($this->progress["placed"]["progress"] < $this->progress["placed"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}

}
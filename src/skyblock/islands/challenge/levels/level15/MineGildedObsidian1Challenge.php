<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level15;

use core\utils\BlockRegistry;
use pocketmine\player\Player;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use skyblock\islands\challenge\Challenge;

class MineGildedObsidian1Challenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof BlockBreakEvent
		) return false;

		$block = $event->getBlock();

		if(!$block->asItem()->equals(BlockRegistry::GILDED_OBSIDIAN()->asItem(), false, false)) return false;

		$this->progress["blocks"]["progress"]++;

		if($this->progress["blocks"]["progress"] < $this->progress["blocks"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}

}
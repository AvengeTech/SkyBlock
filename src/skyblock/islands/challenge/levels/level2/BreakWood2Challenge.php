<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level2;

use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use skyblock\islands\challenge\Challenge;

class BreakWood2Challenge extends Challenge{

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof BlockBreakEvent
		) return false;

		$block = $event->getBlock();

		if(!$block->asItem()->equals(VanillaBlocks::OAK_LOG()->asItem(), false, false)) return false;

		$this->progress["blocks"]["progress"]++;

		if($this->progress["blocks"]["progress"] < $this->progress["blocks"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}

}
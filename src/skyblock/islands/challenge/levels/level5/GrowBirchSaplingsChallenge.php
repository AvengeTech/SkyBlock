<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level5;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

use skyblock\islands\challenge\Challenge;

class GrowBirchSaplingsChallenge extends Challenge{

	private array $delay = [];

	public function onInteractEvent(PlayerInteractEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$block = $event->getBlock();
		$item = $event->getItem();

		if(!(
			$block->asItem()->equals(VanillaBlocks::BIRCH_SAPLING()->asItem(), false, false) &&
			$item->equals(VanillaItems::BONE_MEAL(), false, false)
		)) return false;

		if(
			isset($this->delay[$player->getXuid()]) && 
			microtime(true) - $this->delay[$player->getXuid()] < 0.5
		) return false;

		$this->delay[$player->getXuid()] = microtime(true);
		$this->progress["grown"]["progress"]++;

		if($this->progress["grown"]["progress"] < $this->progress["grown"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}

}
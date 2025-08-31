<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level4;

use pocketmine\player\Player;
use pocketmine\block\Sapling;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;

use skyblock\islands\challenge\Challenge;

class BonemealSaplingsChallenge extends Challenge{

	private array $delay = [];

	public function onInteractEvent(PlayerInteractEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$item = $event->getItem();
		$block = $event->getBlock();

		if(!(
			$item->equals(VanillaItems::BONE_MEAL(), false, false) && 
			$block instanceof Sapling
		)) return false;

		if(
			isset($this->delay[$player->getXuid()]) && 
			microtime(true) - $this->delay[$player->getXuid()] < 0.5
		) return false;

		$this->delay[$player->getXuid()] = microtime(true);
		$this->progress["saplings"]["progress"]++;

		if($this->progress["saplings"]["progress"] < $this->progress["saplings"]["needed"]) return false;

		$this->progress["saplings"]["progress"] = $this->progress["saplings"]["needed"];
		$this->onCompleted($player);
		return true;
	}

}
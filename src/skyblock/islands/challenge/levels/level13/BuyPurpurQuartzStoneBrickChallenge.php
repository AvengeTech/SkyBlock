<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level13;

use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\shop\event\ShopBuyEvent;
use skyblock\shop\event\ShopEvent;

class BuyPurpurQuartzStoneBrickChallenge extends Challenge{

	public function onShopEvent(ShopEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof ShopBuyEvent
		) return false;

		$item = $event->getShopItem()->getItem();

		if($item->equals(VanillaBlocks::PURPUR()->asItem(), false, false)){
			$this->progress["purpur"]["progress"] += $event->getCount();

			if($this->progress["purpur"]["progress"] < $this->progress["purpur"]["needed"]) return false;

			$this->progress["purpur"]["progress"] = $this->progress["purpur"]["needed"];
		}elseif($item->equals(VanillaBlocks::QUARTZ()->asItem(), false, false)){
			$this->progress["quartz"]["progress"] += $event->getCount();

			if($this->progress["quartz"]["progress"] < $this->progress["quartz"]["needed"]) return false;

			$this->progress["quartz"]["progress"] = $this->progress["quartz"]["needed"];
		}elseif($item->equals(VanillaBlocks::STONE_BRICKS()->asItem(), false, false)){
			$this->progress["stone"]["progress"] += $event->getCount();

			if($this->progress["stone"]["progress"] < $this->progress["stone"]["needed"]) return false;

			$this->progress["stone"]["progress"] = $this->progress["stone"]["needed"];
		}

		if(
			$this->progress["purpur"]["progress"] < $this->progress["purpur"]["needed"] ||
			$this->progress["quartz"]["progress"] < $this->progress["quartz"]["needed"] ||
			$this->progress["stone"]["progress"] < $this->progress["stone"]["needed"]
		) return false;

		$this->onCompleted($player);
		return true;
	}

}
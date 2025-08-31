<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level7;

use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use skyblock\islands\challenge\Challenge;
use skyblock\shop\event\ShopBuyEvent;
use skyblock\shop\event\ShopEvent;

class BuyQuartzBlockChallenge extends Challenge{

	public function onShopEvent(ShopEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof ShopBuyEvent
		) return false;

		$item = $event->getShopItem()->getItem();

		if(!$item->equals(VanillaBlocks::QUARTZ()->asItem(), false, false)) return false;

		$this->progress["bought"]["progress"] += $event->getCount();

		if($this->progress["bought"]["progress"] < $this->progress["bought"]["needed"]) return false;

		$this->progress["bought"]["progress"] = $this->progress["bought"]["needed"];
		$this->onCompleted($player);
		return true;
	}

}
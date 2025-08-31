<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level13;

use pocketmine\player\Player;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use skyblock\islands\challenge\Challenge;
use skyblock\shop\event\ShopBuyEvent;
use skyblock\shop\event\ShopEvent;

class BuyBlackWoolConcreteChallenge extends Challenge{

	public function onShopEvent(ShopEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof ShopBuyEvent
		) return false;

		$item = $event->getShopItem()->getItem();

		if($item->equals(VanillaBlocks::WOOL()->setColor(DyeColor::BLACK())->asItem(), false, false)){
			$this->progress["wool"]["progress"] += $event->getCount();

			if($this->progress["wool"]["progress"] < $this->progress["wool"]["needed"]) return false;

			$this->progress["wool"]["progress"] = $this->progress["wool"]["needed"];
		}elseif($item->equals(VanillaBlocks::CONCRETE()->setColor(DyeColor::BLACK())->asItem(), false, false)){
			$this->progress["concrete"]["progress"] += $event->getCount();

			if($this->progress["concrete"]["progress"] < $this->progress["concrete"]["needed"]) return false;

			$this->progress["concrete"]["progress"] = $this->progress["concrete"]["needed"];
		}

		if(
			$this->progress["wool"]["progress"] < $this->progress["wool"]["needed"] ||
			$this->progress["concrete"]["progress"] < $this->progress["concrete"]["needed"]
		) return false;

		$this->onCompleted($player);
		return true;
	}

}
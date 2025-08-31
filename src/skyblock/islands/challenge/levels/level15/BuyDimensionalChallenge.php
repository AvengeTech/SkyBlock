<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level15;

use core\utils\BlockRegistry;
use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\shop\event\ShopBuyEvent;
use skyblock\shop\event\ShopEvent;

class BuyDimensionalChallenge extends Challenge{

	public function onShopEvent(ShopEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof ShopBuyEvent
		) return false;

		$item = $event->getShopItem()->getItem();

		if(!$item->equals(BlockRegistry::DIMENSIONAL_BLOCK()->asItem(), false, false)) return false;

		$this->onCompleted($player);
		return true;
	}

}
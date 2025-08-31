<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level14;

use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\shop\event\ShopBuyEvent;
use skyblock\shop\event\ShopEvent;

class BuyMagmaChallenge extends Challenge{

	public function onShopEvent(ShopEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof ShopBuyEvent
		) return false;

		$item = $event->getShopItem()->getItem();

		if(!$item->equals(VanillaBlocks::MAGMA()->asItem(), false, false)) return false;

		$this->onCompleted($player);
		return true;
	}

}
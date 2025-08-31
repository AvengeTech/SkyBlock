<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level10;

use core\utils\BlockRegistry;
use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;
use skyblock\shop\event\ShopBuyEvent;
use skyblock\shop\event\ShopEvent;

class BuyAutominerChallenge extends Challenge{

	public function onShopEvent(ShopEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof ShopBuyEvent
		) return false;

		$item = $event->getShopItem()->getItem();

		if(!$item->equals(BlockRegistry::AUTOMINER()->asItem(), false, false)) return false;

		$this->onCompleted($player);
		return true;
		
	}

}
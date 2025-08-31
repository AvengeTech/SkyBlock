<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level13;

use core\utils\BlockRegistry;
use pocketmine\player\Player;
use skyblock\generators\tile\OreGenerator;
use skyblock\islands\challenge\Challenge;
use skyblock\shop\event\ShopBuyEvent;
use skyblock\shop\event\ShopEvent;

class BuyGlowingObsidianGeneratorChallenge extends Challenge{

	public function onShopEvent(ShopEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof ShopBuyEvent
		) return false;

		$item = $event->getShopItem()->getItem();

		if(!(
			$item->equals(BlockRegistry::ORE_GENERATOR()->asItem(), false, false) && 
			$item->getNamedTag()->getInt("type", -1) == OreGenerator::TYPE_GLOWING_OBSIDIAN
		)) return false;

		$this->onCompleted($player);
		return true;
	}
}
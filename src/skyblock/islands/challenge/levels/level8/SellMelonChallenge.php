<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level8;

use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use skyblock\islands\challenge\Challenge;
use skyblock\shop\event\ShopEvent;
use skyblock\shop\event\ShopSellEvent;

class SellMelonChallenge extends Challenge{

	public function onShopEvent(ShopEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof ShopSellEvent
		) return false;

		$item = $event->getShopItem()->getItem();

		if(!$item->equals(VanillaItems::MELON(), false, false)) return false;

		$this->progress["sold"]["progress"] += $event->getCount();

		if($this->progress["sold"]["progress"] < $this->progress["sold"]["needed"]) return false;

		$this->progress["sold"]["progress"] = $this->progress["sold"]["needed"];
		$this->onCompleted($player);
		return true;
	}

}
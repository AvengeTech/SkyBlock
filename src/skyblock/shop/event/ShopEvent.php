<?php namespace skyblock\shop\event;

use pocketmine\player\Player;
use pocketmine\event\Event;

use skyblock\shop\data\ShopItem;

abstract class ShopEvent extends Event{

	public $shopitem;
	public $count;
	public $player;

	public function __construct(ShopItem $shopitem, int $count, Player $player){
		$this->shopitem = $shopitem;
		$this->count = $count;
		$this->player = $player;
	}

	public function getShopItem() : ShopItem{
		return $this->shopitem;
	}

	public function getCount() : int{
		return $this->count;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

}
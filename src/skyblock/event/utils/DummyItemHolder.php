<?php namespace skyblock\event\utils;

use pocketmine\item\Item;

class DummyItemHolder{
	
	public function __construct(
		public Item $item
	){}

	public function getItem() : Item{
		return $this->item;
	}

	public function getId() : int{
		return $this->getItem()->getTypeId();
	}

	public function getCount() : int{
		return $this->getItem()->getCount();
	}

}
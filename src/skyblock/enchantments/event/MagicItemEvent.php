<?php namespace skyblock\enchantments\event;

use pocketmine\event\Event;

use pocketmine\item\Item;

class MagicItemEvent extends Event{

	public $item;

	public function __construct(Item $item){
		$this->item = $item;
	}

	public function getItem() : Item{
		return $this->item;
	}

	public function setItem(Item $item) : bool{
		$olditem = $this->getItem();
		if($olditem === $item) return false;
		$this->item = $item;
		return true;
	}

}
<?php namespace skyblock\event;

use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;

use skyblock\event\utils\DummyItemHolder;

class AutoInventoryCollectEvent extends Event{

	public function __construct(
		public Player $player,
		public Item $collected,
		public $leftover = null
	){}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getItem() : Item{
		return $this->getCollected();
	}

	public function getCollected(){
		return $this->collected;
	}

	public function getLeftover(){
		return $this->leftover;
	}

}
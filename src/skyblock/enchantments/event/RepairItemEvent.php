<?php namespace skyblock\enchantments\event;

use pocketmine\item\Item;
use pocketmine\{
	player\Player,
	Server
};

class RepairItemEvent extends MagicItemEvent{

	public $player;

	public $cost;

	public function __construct(Item $item, Player $player, int $cost = 0){
		parent::__construct($item);
		$this->player = $player->getName();
		$this->cost = $cost;
	}

	public function getPlayer() : ?Player{
		return Server::getInstance()->getPlayerExact($this->player);
	}

	public function getCost() : int{
		return $this->cost;
	}

	public function setCost(int $cost) : void{
		$this->cost = $cost;
	}

}
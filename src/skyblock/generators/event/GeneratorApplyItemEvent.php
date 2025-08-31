<?php namespace skyblock\generators\event;

use pocketmine\item\Item;
use pocketmine\player\Player;

use skyblock\generators\tile\{
	AutoMiner,
	DimensionalTile,
	OreGenerator
};

class GeneratorApplyItemEvent extends GeneratorEvent{

	public function __construct(
		private DimensionalTile|OreGenerator|AutoMiner $generator,
		private Player $player, 
		private Item $item
	){
		parent::__construct($generator);
	}

	public function getPlayer() : Player{ return $this->player; }

	public function getItem() : Item{ return $this->item; }
}
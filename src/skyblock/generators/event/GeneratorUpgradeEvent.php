<?php namespace skyblock\generators\event;

use pocketmine\player\Player;

use skyblock\generators\tile\{
	DimensionalTile,
	OreGenerator
};

class GeneratorUpgradeEvent extends GeneratorEvent{

	public function __construct(
		private DimensionalTile|OreGenerator $generator,
		private Player $player, 
		private int $newLevel
	){
		parent::__construct($generator);
	}

	public function getPlayer() : Player{ return $this->player; }

	public function getOldLevel() : int{ return ($this->newLevel - 1); }

	public function getNewLevel() : int{ return $this->newLevel; }
}
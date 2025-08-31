<?php namespace skyblock\hoppers\task;

use pocketmine\scheduler\Task;
use pocketmine\block\Block;
use pocketmine\block\tile\Hopper;

use skyblock\SkyBlock;

class HopperStoreTask extends Task{

	public $block;

	public function __construct(Block $block){
		$this->block = $block;
	}

	public function onRun() : void{
		$tile = $this->block->getPosition()->getWorld()->getTile($this->block->getPosition());
		if($tile instanceof Hopper){
			SkyBlock::getInstance()->hopperStore[$tile->getPosition()->__toString()] = $tile;
		}
	}
}
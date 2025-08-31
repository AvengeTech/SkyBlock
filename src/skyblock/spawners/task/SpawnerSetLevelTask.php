<?php namespace skyblock\spawners\task;

use pocketmine\scheduler\Task;
use pocketmine\block\Block;

use skyblock\spawners\tile\Spawner;

class SpawnerSetLevelTask extends Task{

	public $block;
	public $level;

	public function __construct(Block $block, int $level){
		$this->block = $block;
		$this->level = $level;
	}

	public function onRun() : void{
		$tile = $this->block->getPosition()->getWorld()->getTile($this->block->getPosition());
		if($tile instanceof Spawner){
			$tile->setSpawnerLevel($this->level);
			$tile->setDirty();
		}
	}
}
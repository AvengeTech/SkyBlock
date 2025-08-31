<?php namespace skyblock\generators\event;

use pocketmine\event\Event;

use skyblock\generators\tile\{
    AutoMiner,
    DimensionalTile,
	OreGenerator
};

class GeneratorEvent extends Event{

	public function __construct(
		private DimensionalTile|OreGenerator|AutoMiner $generator
	){}

	public function getGenerator() : DimensionalTile|OreGenerator|AutoMiner{
		return $this->generator;
	}

}
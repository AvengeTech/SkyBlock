<?php namespace skyblock\generators;

use pocketmine\block\tile\TileFactory;

use skyblock\generators\command\GenModeCommand;
use skyblock\generators\tile\{
	OreGenerator as TileOreGenerator,
	AutoMiner as TileAutoMiner,
	DimensionalTile
};

use skyblock\SkyBlock;

class Generators{

	public function __construct(public SkyBlock $plugin){
		$plugin->getServer()->getCommandMap()->register("gen", new GenModeCommand($plugin, "gen", "Enter generator block edit mode"));

		TileFactory::getInstance()->register(TileOreGenerator::class, ["OreGenerator", "minecraft:element_118"]);
		TileFactory::getInstance()->register(TileAutoMiner::class, ["AutoMiner", "minecraft:element_95"]);
		TileFactory::getInstance()->register(DimensionalTile::class, ["Dimensional", "minecraft:element_105"]);
	}

}
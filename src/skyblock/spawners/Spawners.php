<?php namespace skyblock\spawners;

use pocketmine\block\tile\TileFactory;

use skyblock\SkyBlock;
use skyblock\spawners\commands\SpawnerCommand;
use skyblock\spawners\tile\Spawner;

class Spawners{

	const LEVEL_MOB_NAMES = [
		1 => "Pig",
		2 => "Chicken",
		3 => "Sheep",
		4 => "Cow",
		5 => "Spider",
		6 => "Cave Spide",
		7 => "Skeleton",
		8 => "Zombie",
		9 => "Husk",
		10 => "Creeper",
		11 => "Mooshroom",
		12 => "WitherSkeleton",
		13 => "Blaze",
		14 => "Breeze",
		15 => "Enderman",
		16 => "Witch",
		17 => "Iron Golem",
	];

	public function __construct(public SkyBlock $plugin){
		TileFactory::getInstance()->register(Spawner::class, ["MobSpawner", "minecraft:mob_spawner"]);

		$plugin->getServer()->getCommandMap()->register("spawner", new SpawnerCommand($plugin, "spawner", "Opens up the spawner menu"));
	}

}
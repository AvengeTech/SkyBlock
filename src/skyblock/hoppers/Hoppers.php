<?php

namespace skyblock\hoppers;

use pocketmine\block\tile\TileFactory;
use skyblock\hoppers\tile\HopperTile;

class Hoppers {

	public function __construct() {
		TileFactory::getInstance()->register(HopperTile::class, ["Hopper", "minecraft:hopper"]);
	}
}

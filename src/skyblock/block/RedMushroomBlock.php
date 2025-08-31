<?php

namespace skyblock\block;

use core\utils\BlockRegistry;
use pocketmine\block\RedMushroomBlock as PMRedMushroomBlock;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\item\Item;

class RedMushroomBlock extends PMRedMushroomBlock{

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			BlockRegistry::RED_MUSHROOM()->asItem()->setCount(mt_rand(0, 2))
		];
	}
}
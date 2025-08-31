<?php

namespace skyblock\block;

use core\utils\BlockRegistry;
use pocketmine\item\Item;

class BrownMushroomBlock extends RedMushroomBlock{

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			BlockRegistry::BROWN_MUSHROOM()->asItem()->setCount(mt_rand(0, 2))
		];
	}
}
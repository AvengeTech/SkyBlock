<?php

namespace skyblock\hoppers\tile;

use pocketmine\block\tile\Hopper;

class HopperTile extends Hopper {

	public int $nextTick = -1;

	public function tick() {
		$this->nextTick += 20;
	}
}

<?php

namespace skyblock\block;

use core\utils\BlockRegistry;
use pocketmine\block\Air;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class BrownMushroom extends RedMushroom{

	public function getLightLevel() : int{ return 1; }

	public function setHeight() : self{
		if($this->height === -1){
			$this->height = (round(lcg_value() * 100) <= 35 ? mt_rand(5, 6) : mt_rand(7, 8));
			$this->center = $this->height - 2;
		}
		return $this;
	}

	public function canGrow() : bool{
		$this->setHeight();

		$center = $this->getPosition()->add(0, $this->center, 0);
		$bb = new AxisAlignedBB(
			$center->x,
			$center->y,
			$center->z,
			$center->x,
			$center->y,
			$center->z
		);
		$bb->expand(2, 0, 2);
		$bb->offsetTowards(Facing::UP, 6);

		$hasSpace = true;
		$blocks = $this->getPosition()->getWorld()->getCollisionBlocks($bb);

		foreach($blocks as $block){
			if(!$block instanceof Air){
				$hasSpace = false;
				break;
			}
		}

		return $hasSpace;
	}

	protected function grow() : void{
		for($i = 0; $i < $this->height; $i++){
			$this->getPosition()->getWorld()->setBlock($this->getPosition()->add(0, $i, 0), VanillaBlocks::MUSHROOM_STEM());
		}

		$top = $this->getPosition()->add(0, $this->height, 0);
		$skipCoords = [
			"3,3",
			"-3,3",
			"3,-3",
			"-3,-3",
		];

		for($x = -3; $x <= 3; $x++){
			for($z = -3; $z <= 3; $z++){
				if(in_array($x . "," . $z, $skipCoords)) continue;

				$coord = $top->add($x, 0, $z);

				$this->getPosition()->getWorld()->setBlock($coord, BlockRegistry::BROWN_MUSHROOM_BLOCK());
			}
		}
	}
}
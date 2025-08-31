<?php

namespace skyblock\block;

use core\utils\BlockRegistry;
use pocketmine\block\Air;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\RedMushroom as PMRedMushroom;
use pocketmine\block\utils\AgeableTrait;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class RedMushroom extends PMRedMushroom{

	protected int $center = -1;
	protected int $height = -1;
	protected bool $hasGrown = false;

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer && $this->canGrow()){
			$block = clone $this;
			$block->hasGrown = true;

			if(BlockEventHelper::grow($this, $block, $player)){
				$this->grow();
				$item->pop();
			}

			return true;
		}

		return false;
	}

	public function setHeight() : self{
		if($this->height === -1){
			$this->height = (round(lcg_value() * 100) <= 65 ? mt_rand(5, 6) : mt_rand(7, 8));
			$this->center = $this->height - 2;
		}
		return $this;
	}

	public function hasGrown() : bool{ return $this->hasGrown(); }

	public function setGrown(bool $grown) : self{
		$this->hasGrown = $grown;
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
		$bb->expand(2, 2, 2);

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
		$coords = [
			[-1, 0, -1],
			[-1, 0, 0],
			[1, 0, -1],
			[0, 0, -1],
			[0, 0, 0],
			[0, 0, 1],
			[-1, 0, 1],
			[1, 0, 0],
			[1, 0, 1]

		];

		foreach($coords as [$x, $y, $z]){
			$coord = $top->add($x, $y, $z);

			$this->getPosition()->getWorld()->setBlock($coord, BlockRegistry::RED_MUSHROOM_BLOCK());
		}

		$center = $this->getPosition()->add(0, $this->center, 0);
		$east = $center->getSide(Facing::EAST, 2);
		$west = $center->getSide(Facing::WEST, 2);

		$eastWestCoords = [
			[0, -1, -1],
			[0, -1, 0],
			[0, -1, 1],
			[0, 0, -1],
			[0, 0, 0],
			[0, 0, 1],
			[0, 1, -1],
			[0, 1, 0],
			[0, 1, 1]
		];

		foreach($eastWestCoords as [$x, $y, $z]){
			$eastCoord = $east->add($x, $y, $z);
			$westCoord = $west->add($x, $y, $z);

			$this->getPosition()->getWorld()->setBlock($eastCoord, BlockRegistry::RED_MUSHROOM_BLOCK());
			$this->getPosition()->getWorld()->setBlock($westCoord, BlockRegistry::RED_MUSHROOM_BLOCK());
		}

		$north = $center->getSide(Facing::NORTH, 2);
		$south = $center->getSide(Facing::SOUTH, 2);

		$northSouthCoords = [
			[-1, -1, 0],
			[-1, 0, 0],
			[-1, 1, 0],
			[0, -1, 0],
			[0, 0, 0],
			[0, 1, 0],
			[1, -1, 0],
			[1, 0, 0],
			[1, 1, 0]
		];

		foreach($northSouthCoords as [$x, $y, $z]){
			$northCoord = $north->add($x, $y, $z);
			$southCoord = $south->add($x, $y, $z);

			$this->getPosition()->getWorld()->setBlock($northCoord, BlockRegistry::RED_MUSHROOM_BLOCK());
			$this->getPosition()->getWorld()->setBlock($southCoord, BlockRegistry::RED_MUSHROOM_BLOCK());
		}
	}
}
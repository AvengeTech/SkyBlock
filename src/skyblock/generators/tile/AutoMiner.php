<?php

namespace skyblock\generators\tile;

use pocketmine\block\{
	Air,
	Element,
	VanillaBlocks,
	tile\Tile,
	tile\Container
};
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\{
    AxisAlignedBB,
    Facing,
	Vector3
};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\{
	World,
	particle\BlockBreakParticle
};
use skyblock\pets\Structure;
use skyblock\pets\types\IslandPet;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\spawners\block\MobSpawner;

class AutoMiner extends Tile{

	const DATA_HORIZONTAL = 0;
	const DATA_VERTICAL = 1;
	
	const TAG_EXTENDER = "extender";

	public bool $initiated = false;

	private int $horizontalExtender = 0;
	private int $verticalExtender = 0;

	public function __construct(
		World $world, 
		Vector3 $pos
	){
		parent::__construct($world, $pos);
		$world->scheduleDelayedBlockUpdate($pos, 100);
	}

	public function getHorizontalExtender() : int{ return $this->horizontalExtender; }

	public function setHorizontalExtender(int $extender) : self{
		$this->horizontalExtender = $extender;

		return $this;
	}

	public function getVerticalExtender() : int{ return $this->verticalExtender; }

	public function setVerticalExtender(int $extender) : self{
		$this->verticalExtender = $extender;

		return $this;
	}

	public function copyDataFromItem(Item $item): void{
		parent::copyDataFromItem($item);

		$this->initiated = true;

		$this->readSaveData($item->getNamedTag());
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$extender = $nbt->getIntArray(self::TAG_EXTENDER, [
			self::DATA_HORIZONTAL => 0, self::DATA_VERTICAL => 0
		]);

		$this->horizontalExtender = $extender[self::DATA_HORIZONTAL];
		$this->verticalExtender = $extender[self::DATA_VERTICAL];
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setIntArray(self::TAG_EXTENDER, [
			self::DATA_HORIZONTAL => $this->horizontalExtender, self::DATA_VERTICAL => $this->verticalExtender
		]);
	}

	public function canUpdate() : bool{
		foreach ($this->getPosition()->getWorld()->getEntities() as $entity) {
			if (
				$entity instanceof SkyBlockPlayer &&
				!$entity->isAFK()
			) return true;
		}
		return false;
	}

	public function onUpdate() : bool{
		$chest = $this->getBlock()->getSide(Facing::UP);
		$blocks = [];
		$horizontalStart = ($this->horizontalExtender == 1 ? 0 : ($this->horizontalExtender == 2 ? -1 : 1));
		$verticalEnd = ($this->verticalExtender < 1 || $this->verticalExtender > 2 ? 0 : $this->verticalExtender);

		if(
			$horizontalStart === 1 && 
			$verticalEnd === 0
		){
			$blocks[] = $this->getBlock()->getSide(Facing::DOWN);
		}else{
			for($h = 0; $h <= $verticalEnd; $h++){
				for($l = $horizontalStart; $l <= 1; $l++){
					for($w = $horizontalStart; $w <= 1; $w++){
						$length = ($horizontalStart == 1 ? 0 : $l);
						$width = ($horizontalStart == 1 ? 0 : $w);

						$blocks[] = $this->getPosition()->getWorld()->getBlock($this->getBlock()->getSide(Facing::DOWN)->getPosition()->add($width, -$h, $length));
					}
				}
			}
		}

		$bb = new AxisAlignedBB(
			$this->position->x,
			$this->position->y,
			$this->position->z,
			$this->position->x,
			$this->position->y,
			$this->position->z
		);
		$bb->expand(15, 15, 15);

		$owner = null;
		$buffData = [];
		$level = -1;

		foreach ($this->getPosition()->getWorld()->getNearbyEntities($bb) as $entity) {
			if (
				$entity instanceof IslandPet
			) {
				$data = $entity->getPetData();
				if ($data->getIdentifier() === Structure::FOX) {
					$buffData = array_values($data->getBuffData());
					if ($data->getLevel() > $level) {
						$owner = $entity->getOwner();
						$level = $data->getLevel();
					}
				}
			}
		}

		foreach($blocks as $block){
			$sold = false;

			if(
				(
					$block instanceof Air ||
					$block instanceof MobSpawner ||
					$block instanceof Element
				) && $this->getPosition()->getY() >= World::Y_MIN
			) continue;

			if (!(
				$level < 1 ||
				empty($buffData) ||
				count($buffData) < 2
			)) {
				if (round(lcg_value() * 100, 2) <= $buffData[0]) $sold = true;
			}

			if (!($tile = $this->getPosition()->getWorld()->getTile($chest->getPosition())) instanceof Container) continue;
			if (!$tile->getInventory()->canAddItem($block->asItem())) return true;

			if ($sold) {
				$isession = $owner->getGameSession()->getIslands();
				$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();

				$this->getPosition()->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR(), false);

				$price = SkyBlock::getInstance()->getShops()->getValue($block->asItem(), $island->getSizeLevel()) * $buffData[1];

				$owner->addTechits($price);
			} else {
				$drops = $block->getDropsForCompatibleTool(VanillaItems::AIR());
				$left = $tile->getInventory()->addItem(...$drops);

				if ($drops != $left) $this->getPosition()->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR(), false);
			}

			if (!SkyBlock::isLaggy()) $this->getPosition()->getWorld()->addParticle($block->getPosition(), new BlockBreakParticle($block));
		}
		return true;
	}

}
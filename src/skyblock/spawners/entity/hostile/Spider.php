<?php namespace skyblock\spawners\entity\hostile;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Hostile;

class Spider extends Hostile{

	private float $height = 0.9;
	private float $width = 1.4;

	public function __construct(Location $location, ?CompoundTag $nbt = null, bool $movement = false, bool $stacks = true){
		parent::__construct($location, $nbt, $movement, $stacks);

		$this->setCanClimbWalls(true);
	}

	public static function getNetworkTypeId(): string{ return EntityIds::SPIDER; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Spider"; }

	public function getMaxHealth() : int{ return 15; }

	public function getXpDropAmount() : int{ return mt_rand(0, 4); }

	public function getDrops() : array{
		$drops = [
			VanillaItems::STRING()->setCount(mt_rand(0, 1))
		];
		if(round(lcg_value() * 100) <= 25){
			$drops[] = VanillaItems::SPIDER_EYE();
		}

		return $drops;
	}
}
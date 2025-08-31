<?php 

namespace skyblock\spawners\entity\passive;

use core\utils\ItemRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Passive;

class Mooshroom extends Passive{

	private float $height = 1.3;
	private float $width = 0.9;

	public static function getNetworkTypeId(): string{ return EntityIds::MOOSHROOM; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Mooshroom"; }

	public function getMaxHealth() : int{ return 20; }

	public function getXpDropAmount() : int{ return mt_rand(2, 7); }

	public function getDrops() : array{
		$drops =  [
			VanillaBlocks::RED_MUSHROOM()->asItem()->setCount(mt_rand(0, 1)),
			VanillaBlocks::BROWN_MUSHROOM()->asItem()->setCount(mt_rand(0, 1)),
		];

		if(round(lcg_value() * 100, 4) <= 0.0155){
			$drops[] = ItemRegistry::WHITE_MUSHROOM()->setCount(mt_rand(1, 2));
		}

		$items[] = ($this->isOnFire() ? VanillaItems::STEAK() : VanillaItems::RAW_BEEF());

		return $drops;
	}
}
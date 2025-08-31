<?php 

namespace skyblock\spawners\entity\passive;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Passive;

class IronGolem extends Passive{

	private float $height = 2.9;
	private float $width = 1.4;

	public static function getNetworkTypeId(): string{ return EntityIds::IRON_GOLEM; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Iron Golem"; }

	public function getMaxHealth() : int{ return 40; }

	public function getXpDropAmount() : int{ return mt_rand(15, 30); }

	public function getDrops() : array{
		$items = [
			VanillaBlocks::POPPY()->asItem()->setCount(3, 7), // 225 - 525
			VanillaItems::IRON_INGOT()->setCount(5, 15) // 40 - 120
		];

		if(round(lcg_value() * 100, 2) <= 37.5){
			$items[] = VanillaBlocks::IRON()->asItem()->setCount(10, 20); // 720 - 1440
		}

		return $items;
	}
}
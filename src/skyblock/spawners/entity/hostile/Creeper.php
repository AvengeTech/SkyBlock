<?php namespace skyblock\spawners\entity\hostile;

use core\utils\ItemRegistry;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Hostile;

class Creeper extends Hostile{

	private float $height = 1.8;
	private float $width = 0.6;

	public static function getNetworkTypeId(): string{ return EntityIds::CREEPER; }

	protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Creeper"; }

	public function getMaxHealth() : int{ return 20; }

	public function getXpDropAmount() : int{ return mt_rand(2, 7); }

	public function getDrops() : array{
		$items = [VanillaItems::GUNPOWDER()->setCount(mt_rand(0, 2))];

		if(round(lcg_value() * 100, 4) <= 2.0475){
			$items[] = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::CREEPER())->asItem();
		}
		if(round(lcg_value() * 100, 4) <= 0.0155){
			$items[] = VanillaItems::DISC_FRAGMENT_5();
		}

		return $items;
	}
}
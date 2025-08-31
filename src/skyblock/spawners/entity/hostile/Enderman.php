<?php namespace skyblock\spawners\entity\hostile;

use core\utils\ItemRegistry;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\item\VanillaItems;
use skyblock\spawners\entity\Hostile;

class Enderman extends Hostile{

	private float $height = 1.8;
	private float $width = 0.3;

	public static function getNetworkTypeId(): string{ return EntityIds::ENDERMAN; }

	protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Enderman"; }

	public function getMaxHealth() : int{ return 30; }

	public function getXpDropAmount() : int{ return mt_rand(5, 15); }

	public function getDrops() : array{
		$items = [VanillaItems::ENDER_PEARL()->setCount(mt_rand(0, 1))];

		if(round(lcg_value() * 100, 4) <= 4.0257){
			$items[] = ItemRegistry::EYE_OF_ENDER()->setCount(mt_rand(0, 2));
		}

		if(round(lcg_value() * 100, 4) <= 0.0035){
			$items[] = ItemRegistry::JEWEL_OF_THE_END()->setCount(mt_rand(0, 2));
		}

		return $items;
	}
}
<?php namespace skyblock\spawners\entity\passive;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Passive;

class Chicken extends Passive{

	private float $height = 1.0;
	private float $width = 1.0;

	public static function getNetworkTypeId() : string{ return EntityIds::CHICKEN; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Chicken"; }

	public function getMaxHealth() : int{ return 10; }

	public function getXpDropAmount() : int{ return mt_rand(0, 3); }

	public function getDrops() : array{
		$items = [VanillaItems::FEATHER()->setCount(mt_rand(0, 2))];
		$items[] = ($this->isOnFire() ? VanillaItems::COOKED_CHICKEN() : VanillaItems::RAW_CHICKEN());
		return $items;
	}
}
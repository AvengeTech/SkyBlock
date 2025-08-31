<?php namespace skyblock\spawners\entity\passive;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Passive;

class Cow extends Passive{

	private float $height = 1.0;
	private float $width = 1.5;

	public static function getNetworkTypeId() : string{ return EntityIds::COW; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Cow"; }

	public function getMaxHealth() : int{ return 15; }

	public function getXpDropAmount() : int{ return mt_rand(0, 4); }

	public function getDrops() : array{
		$items = [VanillaItems::LEATHER()->setCount(mt_rand(0, 2))];
		$items[] = ($this->isOnFire() ? VanillaItems::STEAK() : VanillaItems::RAW_BEEF());
		return $items;
	}
}
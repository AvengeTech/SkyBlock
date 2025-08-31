<?php namespace skyblock\spawners\entity\hostile;

use core\utils\ItemRegistry;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Hostile;

class Breeze extends Hostile{

	private float $height = 1.77;
	private float $width = 0.6;

	public static function getNetworkTypeId(): string{ return EntityIds::BREEZE; }

	protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Breeze"; }

	public function getMaxHealth() : int{ return 25; }

	public function getXpDropAmount() : int{ return mt_rand(5, 10); }

	public function getDrops() : array{
		return [ItemRegistry::BREEZE_ROD()->setCount(mt_rand(0, 2))];
	}
}
<?php namespace skyblock\spawners\entity\hostile;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Hostile;

class Blaze extends Hostile{

	private float $height = 1.8;
	private float $width = 0.6;

	public static function getNetworkTypeId(): string{ return EntityIds::BLAZE; }

	protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Blaze"; }

	public function getMaxHealth() : int{ return 25; }

	public function getXpDropAmount() : int{ return mt_rand(4, 8); }

	public function getDrops() : array{
		return [VanillaItems::BLAZE_ROD()->setCount(mt_rand(0, 2))];
	}

}
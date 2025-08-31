<?php namespace skyblock\spawners\entity\passive;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Passive;

class Pig extends Passive{

	private float $height = 1.0;
	private float $width = 1.5;

	public static function getNetworkTypeId() : string{ return EntityIds::PIG; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Pig"; }

	public function getMaxHealth() : int{ return 10; }

	public function getXpDropAmount() : int{ return mt_rand(0, 3); }

	public function getDrops() : array{
		$item = ($this->isOnFire() ? VanillaItems::COOKED_PORKCHOP() : VanillaItems::RAW_PORKCHOP());
		return [$item->setCount(1, 3)];
	}
}
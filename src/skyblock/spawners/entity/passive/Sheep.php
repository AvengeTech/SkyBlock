<?php 

namespace skyblock\spawners\entity\passive;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Passive;

class Sheep extends Passive{

	private float $height = 1.3;
	private float $width = 0.9;

	public static function getNetworkTypeId(): string{ return EntityIds::SHEEP; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Sheep"; }

	public function getMaxHealth() : int{ return 15; }

	public function getXpDropAmount() : int{ return mt_rand(0, 3); }

	public function getDrops() : array{
		$items = [VanillaBlocks::WOOL()->asItem()];
		$items[] = ($this->isOnFire() ? VanillaItems::COOKED_MUTTON() : VanillaItems::RAW_MUTTON());
		return $items;
	}
}
<?php namespace skyblock\spawners\entity\hostile;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Hostile;

class Witch extends Hostile{

	private float $height = 1.9;
	private float $width = 0.6;

	public static function getNetworkTypeId(): string{ return EntityIds::WITCH; }

	protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Witch"; }

	public function getMaxHealth() : int{ return 35; }

	public function getXpDropAmount() : int{ return mt_rand(10, 20); }

	public function getDrops() : array{
		$drops = [VanillaItems::REDSTONE_DUST()->setCount(mt_rand(0, 3))];
		
		foreach([
			VanillaItems::GLOWSTONE_DUST(),
			VanillaItems::SUGAR(),
			VanillaItems::SPIDER_EYE(),
			VanillaItems::GUNPOWDER(),
			VanillaItems::STICK()
		] as $item){
			if(round(lcg_value() * 100) <= 65){
				$drops[] = $item->setCount(mt_rand(1, 3));
			}
		}

		return $drops;
	}
}
<?php namespace skyblock\spawners\entity\hostile;

use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\spawners\entity\Hostile;

class Skeleton extends Hostile{

	private float $height = 1.99;
	private float $width = 0.6;

	public static function getNetworkTypeId(): string{ return EntityIds::SKELETON; }

	protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Skeleton"; }

	public function getMaxHealth() : int{ return 20; }

	public function getXpDropAmount() : int{ return mt_rand(2, 5); }

	public function getDrops() : array{
		$items = array_merge(parent::getDrops(), [
			VanillaItems::ARROW()->setCount(mt_rand(0, 2)),
			VanillaItems::BONE()->setCount(mt_rand(0, 2))
		]);

		if(round(lcg_value() * 100, 4) <= 2.0475){
			$items[] = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::SKELETON())->asItem();
		}

		return $items;
	}

}
<?php namespace skyblock\spawners\entity\hostile;

use pocketmine\block\utils\MobHeadType;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use skyblock\spawners\entity\Hostile;

class WitherSkeleton extends Hostile{

	private float $height = 2.412;
	private float $width = 0.864;

	public function __construct(Location $location, ?CompoundTag $nbt = null, bool $movement = false, bool $stacks = true){
		parent::__construct($location, $nbt, $movement, $stacks);

		if($movement) $this->setItemInHand(($item = VanillaItems::STONE_SWORD())->setDamage($item->getMaxDurability() - ($item->getMaxDurability() * (0.01 * mt_rand(5, 20)))));
	}

	public static function getNetworkTypeId(): string{ return EntityIds::WITHER_SKELETON; }

	protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	public function getName() : string{ return "Wither Skeleton"; }

	public function getSoundPrefix() : string{ return "witherskeleton"; }

	public function getMaxHealth() : int{ return 25; }

	public function getXpDropAmount() : int{ return mt_rand(3, 8); }

	public function getDrops() : array{
		$array = [
			VanillaItems::COAL()->setCount(mt_rand(0, 1)),
			VanillaItems::BONE()->setCount(mt_rand(1, 3)),
			VanillaBlocks::WITHER_ROSE()->asItem()->setCount(0, 2)
		];

		if(round(lcg_value() * 100, 4) <= 2.0475){
			$array[] = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::WITHER_SKELETON())->asItem();
		}

		if(round(lcg_value() * 100, 4) <= 0.0155){
			$drops[] = ItemRegistry::WITHERED_BONE()->setCount(mt_rand(1, 2));
		}

		return array_merge(parent::getDrops(), $array);
	}

}
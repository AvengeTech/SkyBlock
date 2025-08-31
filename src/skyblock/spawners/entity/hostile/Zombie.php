<?php namespace skyblock\spawners\entity\hostile;

use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\item\Durable;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use skyblock\spawners\entity\Hostile;

class Zombie extends Hostile{

	private float $height = 1.9;
	private float $width = 0.6;

	protected bool $isBaby = false;
	
	public function __construct(Location $location, ?CompoundTag $nbt = null, bool $movement = false, bool $stacks = true){
		if(!$stacks) $this->isBaby = (mt_rand(1, 3) === 1);

		parent::__construct($location, $nbt, $movement, $stacks);

		if($this->isBaby) $this->setScale(0.5);

		if($movement){
			if($this->isBaby){
				$this->setMovementSpeed(1.0);
			}else{
				$items = [
					"hand" => [
						VanillaItems::IRON_SWORD(),
						VanillaItems::IRON_SHOVEL()
					],
					"helmet" => [
						VanillaItems::LEATHER_CAP(),
						VanillaItems::GOLDEN_HELMET(),
						VanillaItems::CHAINMAIL_HELMET(),
						VanillaItems::IRON_HELMET()
					],
					"chestplate" => [
						VanillaItems::LEATHER_TUNIC(),
						VanillaItems::GOLDEN_CHESTPLATE(),
						VanillaItems::CHAINMAIL_CHESTPLATE(),
						VanillaItems::IRON_CHESTPLATE()
					],
					"leggings" => [
						VanillaItems::LEATHER_PANTS(),
						VanillaItems::GOLDEN_LEGGINGS(),
						VanillaItems::CHAINMAIL_LEGGINGS(),
						VanillaItems::IRON_LEGGINGS()
					],
					"boots" => [
						VanillaItems::LEATHER_BOOTS(),
						VanillaItems::GOLDEN_BOOTS(),
						VanillaItems::CHAINMAIL_BOOTS(),
						VanillaItems::IRON_BOOTS()
					]
				];
	
				/** @var Durable $item */
				$item = $items["hand"][array_rand($items["hand"])];

				if(mt_rand(1, 5) === 1) $this->setItemInHand($item->setDamage($item->getMaxDurability() - ($item->getMaxDurability() * (0.01 * mt_rand(5, 20)))));
				if(mt_rand(1, 5) === 1) $this->getArmorInventory()->setHelmet(($item = $items["helmet"][array_rand($items["helmet"])])->setDamage($item->getMaxDurability() - ($item->getMaxDurability() * (0.01 * mt_rand(5, 20)))));
				if(mt_rand(1, 5) === 1) $this->getArmorInventory()->setChestplate(($item = $items["chestplate"][array_rand($items["chestplate"])])->setDamage($item->getMaxDurability() - ($item->getMaxDurability() * (0.01 * mt_rand(5, 20)))));
				if(mt_rand(1, 5) === 1) $this->getArmorInventory()->setLeggings(($item = $items["leggings"][array_rand($items["leggings"])])->setDamage($item->getMaxDurability() - ($item->getMaxDurability() * (0.01 * mt_rand(5, 20)))));
				if(mt_rand(1, 5) === 1) $this->getArmorInventory()->setHelmet(($item = $items["boots"][array_rand($items["boots"])])->setDamage($item->getMaxDurability() - ($item->getMaxDurability() * (0.01 * mt_rand(5, 20)))));
			}
		}
	}

	public static function getNetworkTypeId(): string{ return EntityIds::ZOMBIE; }

	protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo($this->height, $this->width); }

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);
		$properties->setGenericFlag(EntityMetadataFlags::BABY, $this->isBaby);
	}

	public function getName() : string{ return "Zombie"; }

	public function getSoundPrefix() : string{ return "zombie"; }

	public function getMaxHealth() : int{ return 20; }

	public function getXpDropAmount() : int{ return mt_rand(2, 5); }

	public function getDrops() : array{
		$drops = [
			VanillaItems::ROTTEN_FLESH()->setCount(mt_rand(0, 2))
		];
		if(mt_rand(0, 199) < 5){
			switch(mt_rand(0, 2)){
				case 0:
					$drops[] = VanillaItems::IRON_INGOT();
					break;
				case 1:
					$drops[] = VanillaItems::CARROT();
					break;
				case 2:
					$drops[] = VanillaItems::POTATO();
					break;
			}
		}

		if(round(lcg_value() * 100, 4) <= 2.0475){
			$drops[] = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::ZOMBIE())->asItem();
		}

		return ($this->isBaby ? [] : array_merge(parent::getDrops(), $drops));
	}
}
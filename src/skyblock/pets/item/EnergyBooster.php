<?php

namespace skyblock\pets\item;

use core\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use skyblock\enchantments\EnchantmentData as ED;

class EnergyBooster extends PetFeed{

	const TAG_RARITY = "rarity";
	const TAG_ENERGY = "energy";

	private int $rarity = ED::RARITY_COMMON;
	private float $energy = 0;

	public function setup(int $rarity, float $energy = -1) : self{
		$this->rarity = $rarity;

		$this->setEnergy($energy);
		return $this;
	}

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);
		$this->setCustomName(TF::RESET . TF::YELLOW . "Energy " . TF::BLUE . "Booster");
		$lores = [];
		$lores[] = TF::GRAY . "Use this to give your pet";
		$lores[] = TF::GRAY . "a boost of energy."; 
		$lores[] = " ";
		$lores[] = TF::YELLOW . "Energy: " . round($this->energy, 2); 
		$lores[] = " ";
		$lroes[] = TF::GRAY . "Tap/Click on your pet to use this item."; 
		foreach($lores as $key => $lore) $lores[$key] = TF::RESET . $lore; 

		$this->setLore($lores);
		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([])); 
		return $this;
	}

	public function getRarity() : int{ return $this->rarity; }

	public function getEnergy() : float{ return $this->energy; }

	public function setEnergy(float $energy = -1) : self{
		$this->energy = ($energy > -1 ? $energy : mt_rand(1, 4) * match ($this->rarity) {
			ED::RARITY_COMMON => 5,
			ED::RARITY_UNCOMMON => 10,
			ED::RARITY_RARE => 15,
			ED::RARITY_LEGENDARY => 20,
			ED::RARITY_DIVINE => 25
		});
		return $this;
	}

	protected function deserializeCompoundTag(CompoundTag $tag): void{
		parent::deserializeCompoundTag($tag);

		$this->energy = $tag->getDouble(self::TAG_ENERGY, 0);
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		$tag->setDouble(self::TAG_ENERGY, $this->energy);
	}
}
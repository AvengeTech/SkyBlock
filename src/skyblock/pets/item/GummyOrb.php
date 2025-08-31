<?php

namespace skyblock\pets\item;

use core\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use skyblock\enchantments\EnchantmentData as ED;

class GummyOrb extends PetFeed{

	const TAG_RARITY = "rarity";
	const TAG_EXP = "exp";

	private int $rarity = ED::RARITY_COMMON;
	private int $exp = 0;

	public function setup(int $rarity, int $exp = -1) : self{
		$this->rarity = $rarity;
		
		$this->setXP($exp);
		return $this;
	}

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);
		$this->setCustomName(TF::RESET . TF::LIGHT_PURPLE . "Gummy " . TF::DARK_PURPLE . "Orb");
		$lores = [];
		$lores[] = TF::GRAY . "Use this to give your pet";
		$lores[] = TF::GRAY . "a boost of exp.";
		$lores[] = " ";
		$lores[] = TF::YELLOW . "EXP: " . $this->exp;
		$lores[] = " ";
		$lroes[] = TF::GRAY . "Tap/Click on your pet to use this item.";
		foreach($lores as $key => $lore) $lores[$key] = TF::RESET . $lore;

		$this->setLore($lores);

		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([]));
		return $this;
	}

	public function getRarity() : int{ return $this->rarity; }

	public function getXP() : int{ return $this->exp; }

	public function setXP(int $exp = -1) : self{
		$this->exp = ($exp !== -1 ? $exp : mt_rand(1, 4) * match($this->rarity){
			ED::RARITY_COMMON => 25,
			ED::RARITY_UNCOMMON => 50,
			ED::RARITY_RARE => 100,
			ED::RARITY_LEGENDARY => 250,
			ED::RARITY_DIVINE => 500
		});
		return $this;
	}

	protected function deserializeCompoundTag(CompoundTag $tag): void{
		parent::deserializeCompoundTag($tag);

		$this->rarity = $tag->getInt(self::TAG_RARITY, ED::RARITY_COMMON);
		$this->exp = $tag->getInt(self::TAG_EXP, 0);
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		$tag->setInt(self::TAG_RARITY, $this->rarity);
		$tag->setInt(self::TAG_EXP, $this->exp);
	}
}
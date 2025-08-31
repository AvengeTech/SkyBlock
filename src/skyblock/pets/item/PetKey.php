<?php

namespace skyblock\pets\item;

use core\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class PetKey extends Item{

	const TAG_INIT = "init";

	public function getMaxStackSize() : int{ return 16; }

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);

		$this->setCustomName(TF::RESET . TF::MINECOIN_GOLD . "Pet Key");
		$lores = [];
		$lores[] = TF::GRAY . "Use this item to unlock";
		$lores[] = TF::GRAY . "a pet box";
		$lores[] = " ";
		$lores[] = TF::GRAY . "This can only be used one time.";
		foreach($lores as $key => $lore) $lores[$key] = TF::RESET . $lore;

		$this->setLore($lores);

		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([]));
		return $this;
	}

	
	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		if($tag->getByte(self::TAG_INIT, 0) === 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));
	}
}
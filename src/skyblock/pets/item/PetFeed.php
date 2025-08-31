<?php

namespace skyblock\pets\item;

use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

abstract class PetFeed extends Item{
	
	const TAG_INIT = "init";

	abstract public function init() : self;

	public function isInitiated() : bool{ return (bool) $this->getNamedTag()->getByte(self::TAG_INIT, 0); }

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		if($tag->getByte(self::TAG_INIT, 0) === 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));
	}
}
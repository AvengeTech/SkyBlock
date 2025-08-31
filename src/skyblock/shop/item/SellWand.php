<?php namespace skyblock\shop\item;

use pocketmine\item\{
	Item,
};
use pocketmine\nbt\{
	NBT,
	tag\ListTag
};

use core\utils\TextFormat;
use pocketmine\nbt\tag\CompoundTag;

class SellWand extends Item{
	
	public function getMaxStackSize() : int{
		return 64;
	}

	public function isInitiated() : bool{
		return (bool) $this->getNamedTag()->getByte("init", 0);
	}

	public function init() : self{
		$nbt = $this->getNamedTag();
		$nbt->setByte("init", 1);
		$this->setNamedTag($nbt);

		$this->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Sell Wand");
		$lores = [];
		$lores[] = TextFormat::GRAY . "Tap any " . TextFormat::YELLOW . "chest" . TextFormat::GRAY . " on your";
		$lores[] = TextFormat::GRAY . "island with this wand to ";
		$lores[] = TextFormat::GREEN . "sell" . TextFormat::GRAY . " it's contents!";
		$lores[] = " ";
		$lores[] = TextFormat::GRAY . "Can only be used once.";
		foreach($lores as $key => $lore) $lores[$key] = TextFormat::RESET . $lore;

		$this->setLore($lores);

		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		return $this;
	}

	protected function serializeCompoundTag(CompoundTag $tag): void {
		parent::serializeCompoundTag($tag);
		if($tag->getByte("init", 0) == 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));
	}
}
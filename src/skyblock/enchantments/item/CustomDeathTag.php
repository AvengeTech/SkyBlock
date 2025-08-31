<?php namespace skyblock\enchantments\item;

use pocketmine\item\{
	Item,
};
use pocketmine\nbt\{
	NBT,
	tag\ListTag,
	tag\CompoundTag
};

use core\utils\TextFormat;


class CustomDeathTag extends Item{

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

		$this->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Custom Death Tag");
		$lores = [];
		$lores[] = TextFormat::GRAY . "Bring this to the " . TextFormat::DARK_GRAY . TextFormat::BOLD . "Blacksmith" . TextFormat::RESET . TextFormat::GRAY . ",";
		$lores[] = TextFormat::GRAY . "located at " . TextFormat::WHITE . "Spawn" . TextFormat::GRAY . " to add a";
		$lores[] = TextFormat::GRAY . "death message to your sword!";
		$lores[] = " ";
		$lores[] = TextFormat::GRAY . "This can only be used one time.";
		foreach($lores as $key => $lore) $lores[$key] = TextFormat::RESET . $lore;

		$this->setLore($lores);

		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([]));
		return $this;
	}

	protected function serializeCompoundTag(CompoundTag $tag): void {
		parent::serializeCompoundTag($tag);
		$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));
	}

}
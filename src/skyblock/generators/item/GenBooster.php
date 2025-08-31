<?php namespace skyblock\generators\item;

use pocketmine\item\Item;
use pocketmine\nbt\{
	NBT,
	tag\ListTag,
	tag\CompoundTag
};
use core\utils\TextFormat;

class GenBooster extends Item{

	const TAG_INIT = "init";
	const TAG_VALUE = "value";

	private int $value = 0;

	public function setup(int $value = 64) : self{
		$this->value = $value;

		$this->init();

		return $this;
	}

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);

		$this->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Gen Booster");
		$lores = [];
		$lores[] = TextFormat::GRAY . "This Gen Booster is worth";
		$lores[] = TextFormat::YELLOW . number_format($this->getValue()) . " boosts! " . TextFormat::GRAY . "Tap any ore";
		$lores[] = TextFormat::GRAY . "generator or dimensional";
		$lores[] = TextFormat::GRAY . "block to apply!";

		foreach($lores as $key => $lore) $lores[$key] = TextFormat::RESET . $lore;

		$this->setLore($lores);
		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		return $this;
	}

	public function isInitiated() : bool{ return (bool) $this->getNamedTag()->getByte(self::TAG_INIT, 0); }

	public function getValue() : int{ return $this->getNamedTag()->getInt(self::TAG_VALUE, 0); }

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);

		$this->value = $tag->getInt(self::TAG_VALUE, 0);
	}

	protected function serializeCompoundTag(CompoundTag $tag): void {
		parent::serializeCompoundTag($tag);

		if($tag->getByte(self::TAG_INIT, 0) === 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		$tag->setInt(self::TAG_VALUE, $this->value);
	}
}
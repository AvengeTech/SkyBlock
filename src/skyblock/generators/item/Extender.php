<?php

namespace skyblock\generators\item;

use core\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

abstract class Extender extends Item{
	
	const TAG_INIT = "init";
	const TAG_LEVEL = "level";

	const TYPE_HORIZONTAL = 0;
	const TYPE_VERTICAL = 1;

	private int $level = 1;

	public function getMaxStackSize() : int{ return 8; }

	public function setup(int $level) : self{
		$this->level = $level;

		return $this;
	}

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);
		$this->setCustomName(TF::BOLD . ($this->getType() === self::TYPE_HORIZONTAL ? TF::GOLD . "Horizontal" : TF::MINECOIN_GOLD . "Vertical") . " Extender");

		$lores = [];
		$lores[] = TF::GRAY . "Use this item to expand the";
		$lores[] = TF::GRAY . ($this->getType() === self::TYPE_HORIZONTAL ? "horizontal" : "vertical") . " generation of";
		$lores[] = TF::GRAY . "an ore generator or mined area";
		$lores[] = TF::GRAY . "of an autominer.";
		$lores[] = " ";
		$lores[] = TF::AQUA . "Level: " . $this->level;
		$lores[] = " ";
		$lores[] = TF::GRAY . "This can only be used one time.";
		foreach($lores as $key => $lore) $lores[$key] = TF::RESET . $lore;

		$this->setLore($lores);
		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		return $this;
	}

	public function isInitiated() : bool{ return (bool) $this->getNamedTag()->getByte(self::TAG_INIT, 0); }

	public function getLevel() : int{ return $this->level; }

	public function setLevel(int $level) : self{
		$this->level = $level;

		return $this;
	}

	abstract public function getType() : int;

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);

		$this->level = $tag->getInt(self::TAG_LEVEL, 1);
	}

	protected function serializeCompoundTag(CompoundTag $tag): void {
		parent::serializeCompoundTag($tag);

		if($tag->getByte(self::TAG_INIT, 0) === 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		$tag->setInt(self::TAG_LEVEL, $this->level);
	}


}
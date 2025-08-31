<?php

namespace skyblock\generators\item;

use core\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class Solidifier extends Item{
	
	const TAG_INIT = "init";
	const TAG_LEVEL = "level";
	const TAG_RUNS = "runs";

	private int $level = 1;
	private int $runs = 1;

	public function setup(int $level, int $runs) : self{
		$this->level = $level;
		$this->runs = $runs;

		return $this;
	}

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);
		$this->setCustomName(TF::BOLD . TF::LIGHT_PURPLE . "Solidifier");

		$lores = [];
		$lores[] = TF::GRAY . "Use this item to give a";
		$lores[] = TF::GRAY . "ore generator the chance";
		$lores[] = TF::GRAY . "to spawn the block";
		$lores[] = TF::GRAY . "version of an ore.";
		$lores[] = " ";
		$lores[] = TF::DARK_PURPLE . "Level: " . $this->level;
		$lores[] = TF::LIGHT_PURPLE . "Runs: " . $this->runs;
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

	public function getRuns() : int{ return $this->runs; }

	public function setRuns(int $runs) : self{
		$this->runs = $runs;

		return $this;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);

		$this->level = $tag->getInt(self::TAG_LEVEL, 1);
		$this->runs = $tag->getInt(self::TAG_RUNS, 1);
	}

	protected function serializeCompoundTag(CompoundTag $tag): void {
		parent::serializeCompoundTag($tag);

		if($tag->getByte(self::TAG_INIT, 0) === 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		$tag->setInt(self::TAG_LEVEL, $this->level);
		$tag->setInt(self::TAG_RUNS, $this->runs);
	}


}
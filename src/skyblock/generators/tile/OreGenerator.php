<?php

namespace skyblock\generators\tile;

use pocketmine\block\{
	Air,
	Block,
	tile\Tile,
	VanillaBlocks
};
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\StringToItemParser;
use pocketmine\math\{
    AxisAlignedBB,
    Facing,
	Vector3
};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};
use skyblock\generators\event\GeneratorUpgradeEvent;
use skyblock\generators\Structure;
use skyblock\generators\tile\AutoMiner;
use skyblock\pets\Structure as PetStructure;
use skyblock\pets\types\IslandPet;

class OreGenerator extends Tile{

	// extender
	const DATA_HORIZONTAL = 0;
	const DATA_VERTICAL = 1;

	// solidifier
	const DATA_LEVEL = 0;
	const DATA_RUNS = 1;

	const TAG_TYPE = "type";
	const TAG_LEVEL = "level";
	const TAG_BOOST = "boost";
	const TAG_EXTENDER = "extender";
	const TAG_SOLIDIFIER = "solidifier";

	const TYPE_COAL = 0;
	const TYPE_IRON = 1;
	const TYPE_REDSTONE = 2;
	const TYPE_LAPIS_LAZULI = 3;
	const TYPE_COPPER = 4;
	const TYPE_GOLD = 5;
	const TYPE_DIAMOND = 6;
	const TYPE_EMERALD = 7;
	const TYPE_OBSIDIAN = 8;
	const TYPE_GLOWING_OBSIDIAN = 9;
	const TYPE_ANCIENT_DEBRIS = 10;
	const TYPE_GILDED_OBSIDIAN = 11;

	const ORES = [
		self::TYPE_COAL => "coal_ore",
		self::TYPE_IRON => "iron_ore",
		self::TYPE_REDSTONE => "redstone_ore",
		self::TYPE_LAPIS_LAZULI => "lapis_lazuli_ore",
		self::TYPE_COPPER => "copper_ore",
		self::TYPE_GOLD => "gold_ore",
		self::TYPE_DIAMOND => "diamond_ore",
		self::TYPE_EMERALD => "emerald_ore",
		self::TYPE_OBSIDIAN => "obsidian",
		self::TYPE_GLOWING_OBSIDIAN => "glowing_obsidian",
		self::TYPE_ANCIENT_DEBRIS => "ancient_debris",
		self::TYPE_GILDED_OBSIDIAN => "gilded_obsidian"
	];

	const BLOCKS = [
		self::TYPE_COAL => "coal_block",
		self::TYPE_IRON => "iron_block",
		self::TYPE_REDSTONE => "redstone_block",
		self::TYPE_LAPIS_LAZULI => "lapis_lazuli_block",
		self::TYPE_COPPER => "copper_block",
		self::TYPE_GOLD => "gold_block",
		self::TYPE_DIAMOND => "diamond_block",
		self::TYPE_EMERALD => "emerald_block",
		self::TYPE_OBSIDIAN => "polished_obsidian",
		self::TYPE_GLOWING_OBSIDIAN => "polished_glowing_obsidian",
		self::TYPE_ANCIENT_DEBRIS => "netherite_block",
		self::TYPE_GILDED_OBSIDIAN => "polished_gilded_obsidian"
	];
	
	public float $cantTickBefore;
	public float $created;
	public bool $initiated = false;
	
	private bool $first = true;
	private bool $firstSpawn = true;
	
	private int $boost = 0;
	private int $level = 1;
	private int $type = 0;

	private int $horizontalExtender = 0;
	private int $verticalExtender = 0;

	private int $solidifierLevel = 0;
	private int $solidifierRuns = 0;

	
	public function __construct(
		World $world, 
		Vector3 $pos
	){
		parent::__construct($world, $pos);

		// $world->scheduleDelayedBlockUpdate($pos, $this->getRate(true) * 20);

		$this->created = microtime(true);
		$this->cantTickBefore = microtime(true) + $this->getRate(true) - 0.25;
	}

	public function first() : bool{
		if($this->first){
			$this->first = false;
			return true;
		}
		return false;
	}

	public function firstSpawn() : bool{
		if($this->firstSpawn){
			$this->firstSpawn = false;
			return true;
		}
		return false;
	}
	
	public function getTypeBlock(bool $original = true, bool $force = false) : Block{
		if(!$original && ($this->solidifierLevel > 0 && $this->solidifierRuns > 0 || $force)){
			$chance = match($this->solidifierLevel){
				1 => mt_rand(1, 100) <= 5,
				2 => mt_rand(1, 100) <= 15,
				3 => mt_rand(1, 100) <= 30,
				4 => mt_rand(1, 100) <= 65,
				default => true
			};

			$this->solidifierRuns--;

			if($this->solidifierRuns <= 0) $this->solidifierLevel = 0;

			$item = StringToItemParser::getInstance()->parse(($chance ? self::BLOCKS[$this->type] : self::ORES[$this->type]));
		}else{
			$item = StringToItemParser::getInstance()->parse(self::ORES[$this->type]);
		}

		$block = ($item instanceof ItemBlock ? $item->getBlock() : VanillaBlocks::AIR());

		return $block;
	}

	public function getLevel() : int{ return $this->level; }

	public function setLevel(int $level) : self{
		$this->level = $level;

		return $this;
	}

	public function getHorizontalExtender() : int{ return $this->horizontalExtender; }

	public function setHorizontalExtender(int $extender) : self{
		$this->horizontalExtender = $extender;

		return $this;
	}

	public function getVerticalExtender() : int{ return $this->verticalExtender; }

	public function setVerticalExtender(int $extender) : self{
		$this->verticalExtender = $extender;

		return $this;
	}

	public function getBoost() : int{ return $this->boost; }

	public function setBoost(int $runs) : self{
		$this->boost = $runs;

		return $this;
	}

	public function addBoost(int $runs) : self{ return $this->setBoost($this->getBoost() + $runs); }

	public function hasBoost() : bool{ return $this->getBoost() >= 1; }
	
	public function getType() : int{ return $this->type; }

	public function setType(int $type) : self{
		$this->type = $type;

		return $this;
	}

	public function getSolidifierLevel() : int{ return $this->solidifierLevel; }

	public function setSolidifierLevel(int $level) : self{
		$this->solidifierLevel = max(0, $level);

		return $this;
	}

	public function hasSolidifierLevel() : bool{ return $this->getSolidifierLevel() > 0; }

	public function getSolidifierRuns() : int{ return $this->solidifierRuns; }

	public function setSolidifierRuns(int $runs) : self{
		$this->solidifierRuns = max(0, $runs);

		return $this;
	}

	public function addSolidifierRuns(int $runs) : self{ return $this->setSolidifierRuns($this->getSolidifierRuns() + $runs); }

	public function hasSolidifierRuns() : bool{ return $this->getSolidifierRuns() >= 1; }

	public function getNextLevelPrice() : int{
		if($this->getLevel() === 0) return -1;
		return $this->canLevelUp() ? Structure::UPGRADE_COSTS[Structure::TYPE_ORE_GENERATOR][$this->getType()][$this->getLevel() + 1] : -1;
	}
	
	public function canLevelUp() : bool{ return isset(Structure::UPGRADE_COSTS[Structure::TYPE_ORE_GENERATOR][$this->getType()][$this->getLevel() + 1]); }

	public function levelUp(Player $player) : bool{
		/** @var SkyBlockPlayer $player */
		if(!$this->canLevelUp()) return false;

		$player->takeTechits($this->getNextLevelPrice());
		$this->setLevel($this->getlevel() + 1);
		$ev = new GeneratorUpgradeEvent($this, $player, $this->getLevel());
		$ev->call();

		return true;
	}

	public function getRate(bool $forSchedule = false) : int{ return (Structure::RATES[Structure::TYPE_ORE_GENERATOR][$this->getLevel()] ?? 10) + ($forSchedule ? 1 : 0); }

	public function getUpgradeCost() : int{
		if($this->getLevel() === 0) return -1;
		return Structure::UPGRADE_COSTS[Structure::TYPE_ORE_GENERATOR][$this->getType()][$this->getLevel() + 1] ?? -1;
	}

	public function copyDataFromItem(Item $item) : void{
		parent::copyDataFromItem($item);

		$this->initiated = true;

		$this->readSaveData($item->getNamedTag());
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->type = $nbt->getInt(self::TAG_TYPE, 1);
		$this->level = $nbt->getInt(self::TAG_LEVEL, 1);
		$this->boost = $nbt->getInt(self::TAG_BOOST, 0);

		$extender = $nbt->getIntArray(self::TAG_EXTENDER, [
			self::DATA_HORIZONTAL => 0, self::DATA_VERTICAL => 0
		]);

		$this->horizontalExtender = $extender[self::DATA_HORIZONTAL];
		$this->verticalExtender = $extender[self::DATA_VERTICAL];

		$solidifier = $nbt->getIntArray(self::TAG_SOLIDIFIER, [
			self::DATA_LEVEL => 0, self::DATA_RUNS => 0
		]);

		$this->solidifierLevel = $solidifier[self::DATA_LEVEL];
		$this->solidifierRuns = $solidifier[self::DATA_RUNS];
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_TYPE, $this->type);
		$nbt->setInt(self::TAG_LEVEL, $this->level);
		$nbt->setInt(self::TAG_BOOST, $this->boost);
		$nbt->setIntArray(self::TAG_EXTENDER, [
			self::DATA_HORIZONTAL => $this->horizontalExtender, self::DATA_VERTICAL => $this->verticalExtender
		]);
		$nbt->setIntArray(self::TAG_SOLIDIFIER, [
			self::DATA_LEVEL => $this->solidifierLevel, self::DATA_RUNS => $this->solidifierRuns
		]);
	}

	public function onUpdate() : bool{
		if($this->first()) return true;
		if(microtime(true) < $this->cantTickBefore) return true;

		$side = $this->getBlock()->getSide(Facing::UP);
		$horizontalStart = ($this->horizontalExtender == 1 ? 0 : ($this->horizontalExtender == 2 ? -1 : 1));
		$verticalEnd = ($this->verticalExtender < 1 || $this->verticalExtender > 2 ? 0 : $this->verticalExtender);

		$bb = new AxisAlignedBB(
			$this->position->x,
			$this->position->y,
			$this->position->z,
			$this->position->x,
			$this->position->y,
			$this->position->z
		);
		$bb->expand(15, 15, 15);

		$buffData = [];
		$level = -1;

		foreach ($this->getPosition()->getWorld()->getNearbyEntities($bb) as $entity) {
			if ($entity instanceof IslandPet) {
				$data = $entity->getPetData();
				if ($data->getIdentifier() === PetStructure::FOX) {
					$buffData = array_values($data->getBuffData());
					if ($data->getLevel() > $level) {
						$level = $data->getLevel();
					}
				}
			}
		}

		$force = false;

		if(
			$horizontalStart === 1 && 
			$verticalEnd === 0
		){
			if(!(
				$level < 1 ||
				empty($buffData)
			)){
				if(round(lcg_value() * 100, 2) <= $buffData[0]) $force = true;
			}

			if($side instanceof Air && $this->getPosition()->getY() < 255){
				$this->getPosition()->getWorld()->setBlock($side->getPosition(), $this->getTypeBlock(false, $force), false);
			}
		}else{
			for($h = 0; $h <= $verticalEnd; $h++){
				for($l = $horizontalStart; $l <= 1; $l++){
					for($w = $horizontalStart; $w <= 1; $w++){
						$length = ($horizontalStart == 1 ? 0 : $l);
						$width = ($horizontalStart == 1 ? 0 : $w);
						$pos = $side->getPosition()->add($width, $h, $length);

						if(!(
							$level < 1 ||
							empty($buffData)
						)){
							if(round(lcg_value() * 100, 2) <= $buffData[0]) $force = true;
						}

						if($this->getPosition()->getWorld()->getBlock($pos) instanceof Air && $pos->getY() < 255){
							$this->getPosition()->getWorld()->setBlock($pos, $this->getTypeBlock(false, $force), false);
						}
					}
				}
			}
		}

		if($this->hasBoost() && !$this->firstSpawn()) $this->boost--;

		for($i = 1; $i <= 4; $i++){
			$amPos = $side->getSide(Facing::UP, $i)->getPosition();

			if(!($tile = $this->getPosition()->getWorld()->getTile($amPos)) instanceof AutoMiner) continue;

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($tile) : void{
				if(!$tile->closed){
					$tile->onUpdate();
				}
			}), 5);
			break;
		}

		return true;
	}
}
<?php 

namespace skyblock\generators\tile;

use core\utils\conversion\LegacyItemIds;
use pocketmine\block\tile\{
	Tile,
	Container
};
use pocketmine\item\Item;
use pocketmine\math\{
	Facing,
	Vector3
};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\World;

use core\utils\GenericSound;
use core\utils\ItemRegistry;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use skyblock\generators\event\GeneratorUpgradeEvent;
use skyblock\generators\Structure;
use skyblock\SkyBlockPlayer;

class DimensionalTile extends Tile{

	const TAG_LEVEL = "level";
	const TAG_BOOST = "boost";

	const DIMENSIONAL_ITEMS = [
		"netherrack",
		"quartz_ore",
		"soul_sand",
		"nether_wart",
		"nether_bricks",
		"ghast_tear",
		"glowstone",
		"end_rod",
		"end_stone",
		"purpur_block",
		"end_bricks",
		"rotten_flesh",
		"gold_nugget",
		"magma_cream",
		"prismarine_shard",
	];
	
	public float $created;
	private bool $first = true;
	private bool $firstSpawn = true;
	private float $cantTickBefore;
	
	private int $level = 1;
	private int $boost = 0;

	public bool $initiated = false;

	public function __construct(
		World $world, 
		Vector3 $pos
	){
		parent::__construct($world, $pos);

		$world->scheduleDelayedBlockUpdate($pos, $this->getRate(true) * 20);

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

	public function getLevel() : int{ return $this->level; }

	public function setLevel(int $level) : self{
		$this->level = $level;

		return $this;
	}

	public function canLevelUp() : bool{
		return isset(Structure::UPGRADE_COSTS[Structure::TYPE_DIMENSIONAL_GENERATOR][$this->getLevel() + 1]);
	}

	public function levelUp(Player $player) : bool{
		/** @var SkyBlockPlayer $player */
		if(!$this->canLevelUp()) return false;

		$player->takeTechits($this->getNextLevelPrice());

		$this->setLevel($this->getlevel() + 1);

		$ev = new GeneratorUpgradeEvent($this, $player, $this->getLevel());
		$ev->call();
		return true;
	}

	public function getNextLevelPrice() : int{ return $this->canLevelUp() ? Structure::UPGRADE_COSTS[Structure::TYPE_DIMENSIONAL_GENERATOR][$this->getLevel() + 1] : -1; }

	public function getRate(bool $forSchedule = false) : int{
		return (Structure::RATES[Structure::TYPE_DIMENSIONAL_GENERATOR][$this->getLevel()] ?? 10) + ($forSchedule ? 1 : 0);
	}
	
	public function getBoost() : int{ return $this->boost; }

	public function setBoost(int $runs) : self{
		$this->boost = max(0, $runs);
		return $this;
	}
	
	public function hasBoost() : bool{ return $this->getBoost() >= 1; }
	
	public function addBoost(int $runs) : self{ return $this->setBoost($this->getBoost() + $runs); }

	public function getUpgradeCost() : int{ return Structure::UPGRADE_COSTS[Structure::TYPE_DIMENSIONAL_GENERATOR][$this->getLevel() + 1] ?? -1; }
	
	public function getTypeItem() : Item{
		$item = StringToItemParser::getInstance()->parse(self::DIMENSIONAL_ITEMS[array_rand(self::DIMENSIONAL_ITEMS)]);

		return (is_null($item) ? VanillaItems::AIR() : $item);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->level = $nbt->getInt(self::TAG_LEVEL, 1);
		$this->boost = $nbt->getInt(self::TAG_BOOST, 0);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_LEVEL, $this->level);
		$nbt->setInt(self::TAG_BOOST, $this->boost);
	}

	public function copyDataFromItem(Item $item) : void{
		parent::copyDataFromItem($item);

		$this->setLevel($item->getNamedTag()->getInt(self::TAG_LEVEL, 1));
		$this->addBoost($item->getNamedTag()->getInt(self::TAG_BOOST, 0));
	}

	public function onUpdate() : bool{
		if($this->first()) return true;
		if(microtime(true) < $this->cantTickBefore) return true;

		$chest = $this->getBlock()->getSide(Facing::UP);

		if(($tile = $this->getPosition()->getWorld()->getTile($chest->getPosition())) instanceof Container){
			$item = $this->getTypeItem();

			if(!$tile->getInventory()->canAddItem($item)) return true;

			$tile->getInventory()->addItem($item);

			$this->getPosition()->getWorld()->addSound($this->getPosition(), new GenericSound($this->getPosition(), 61));

			if($this->hasBoost() && !$this->firstSpawn()) $this->boost--;
		}
		return true;
	}

}
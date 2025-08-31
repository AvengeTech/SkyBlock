<?php namespace skyblock\spawners\tile;

use Exception;
use pocketmine\block\tile\MonsterSpawner;
use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;
use pocketmine\entity\{
	Entity,
	Location
};
use pocketmine\math\{
	AxisAlignedBB,
	Vector3
};
use pocketmine\nbt\tag\{
	CompoundTag,
};
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\World;
use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player
};
use skyblock\spawners\entity\hostile\Blaze;
use skyblock\spawners\entity\hostile\Breeze;
use skyblock\spawners\entity\hostile\CaveSpider;
use skyblock\spawners\entity\hostile\Creeper;
use skyblock\spawners\entity\hostile\Enderman;
use skyblock\spawners\entity\hostile\Husk;
use skyblock\spawners\entity\hostile\Skeleton;
use skyblock\spawners\entity\hostile\Spider;
use skyblock\spawners\entity\hostile\Witch;
use skyblock\spawners\entity\hostile\WitherSkeleton;
use skyblock\spawners\entity\hostile\Zombie;
use skyblock\spawners\entity\Mob;
use skyblock\spawners\entity\passive\Chicken;
use skyblock\spawners\entity\passive\Cow;
use skyblock\spawners\entity\passive\IronGolem;
use skyblock\spawners\entity\passive\Mooshroom;
use skyblock\spawners\entity\passive\Pig;
use skyblock\spawners\entity\passive\Sheep;
use skyblock\spawners\event\SpawnerUpgradeEvent;

class Spawner extends MonsterSpawner{

	const MAX_LEVEL = 17;

	const LEVEL_PRICE = [
		2 => 5000,
		3 => 7500,
		4 => 10000,
		5 => 15000,
		6 => 20000,
		7 => 25000,
		8 => 50000,
		9 => 75000,
		10 => 100000,
		11 => 250000,
		12 => 500000,
		13 => 750000,
		14 => 1000000,
		15 => 2500000,
		16 => 5000000,
		17 => 7500000,
		18 => PHP_INT_MAX
	];

	const LEVEL_ENTITY_IDS = [
		1 => EntityIds::PIG,
		2 => EntityIds::CHICKEN,
		3 => EntityIds::SHEEP,
		4 => EntityIds::COW,
		5 => EntityIds::SPIDER,
		6 => EntityIds::CAVE_SPIDER,
		7 => EntityIds::SKELETON,
		8 => EntityIds::ZOMBIE,
		9 => EntityIds::HUSK,
		10 => EntityIds::CREEPER,
		11 => EntityIds::MOOSHROOM,
		12 => EntityIds::WITHER_SKELETON,
		13 => EntityIds::BLAZE,
		14 => EntityIds::BREEZE,
		15 => EntityIds::ENDERMAN,
		16 => EntityIds::WITCH,
		17 => EntityIds::IRON_GOLEM,
		18 => PHP_INT_MAX
	];

	const LEVEL_ENTITY_CLASSES = [
		1 => Pig::class,
		2 => Chicken::class,
		3 => Sheep::class,
		4 => Cow::class,
		5 => Spider::class,
		6 => CaveSpider::class,
		7 => Skeleton::class,
		8 => Zombie::class,
		9 => Husk::class,
		10 => Creeper::class,
		11 => Mooshroom::class,
		12 => WitherSkeleton::class,
		13 => Blaze::class,
		14 => Breeze::class,
		15 => Enderman::class,
		16 => Witch::class,
		17 => IronGolem::class
	];

	const TAG_ENTITY_ID = "EntityId";
	const TAG_LEVEL = "SpawnerLevel";
	const TAG_LEVEL_ENTITY = "LevelEntity";

	public int $spawnerLevel = 1;
	public int $levelEntity = -1;

	public ?Entity $lastSpawnedEntity = null;

	public int $nextTick = -1;

	public function tick(int $tick): void {
		if ($tick >= $this->nextTick) $this->doUpdate();
	}

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		SkyBlock::getInstance()->spawnerStore[$this->getPosition()->__toString()] = $this;
	}

	public function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_LEVEL, $this->getSpawnerLevel());
		$nbt->setInt(self::TAG_LEVEL_ENTITY, $this->levelEntity);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->setSpawnerLevel($nbt->getInt(self::TAG_LEVEL, 1));
		$this->levelEntity = $nbt->getInt(self::TAG_LEVEL_ENTITY, -1);
	}

	public function getEntityId() : int{
		$map = LegacyEntityIdToStringIdMap::getInstance()->getLegacyToStringMap();
		$reversed = [];
		foreach ($map as $k => $v) $reversed[$v] = $k;
		try {
			return $reversed[self::LEVEL_ENTITY_IDS[$this->getSpawnerLevel()]];
		} catch (Exception) {
			return -1;
		}
	}

	public function getSpawnerLevel() : int{
		return $this->spawnerLevel;
	}

	public function canLevelUp(Player $player) : bool{
		return $player->getTechits() >= self::LEVEL_PRICE[$this->getSpawnerLevel() + 1];
	}

	public function levelUp(?Player $player = null) : void{
		$ev = new SpawnerUpgradeEvent($player, $this->getSpawnerLevel(), $this->getSpawnerLevel() + 1);
		$ev->call();

		$this->setSpawnerLevel(min(self::MAX_LEVEL, $ev->getNewLevel()));

		if($player instanceof Player){
			$player->takeTechits(self::LEVEL_PRICE[$this->getSpawnerLevel()]);
		}
	}

	public function setSpawnerLevel(int $level) : void{
		$this->spawnerLevel = $level;
	}
	
	public function getLevelEntity() : int{
		$map = LegacyEntityIdToStringIdMap::getInstance()->getLegacyToStringMap();
		$reversed = [];
		foreach ($map as $k => $v) $reversed[$v] = $k;
		try {
			return $reversed[self::LEVEL_ENTITY_IDS[($this->levelEntity !== -1 ? $this->levelEntity : $this->getSpawnerLevel())]];
		} catch (Exception) {
			return -1;
		}
	}
	
	public function setLevelEntity(int $level = -1) : void{
		$this->levelEntity = $level;
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_ENTITY_ID, $this->getLevelEntity());
	}

	public function getName() : string{
		return "Mob Spawner";
	}

	public function canUpdate() : bool{
		if($this->getBlock() === null){
			return false;
		}

		$pos = $this->getBlock()->getPosition();
		$distance = 25;
		$bb = new AxisAlignedBB(
			$pos->x - $distance,
			$pos->y - $distance,
			$pos->z - $distance,
			$pos->x + $distance,
			$pos->y + $distance,
			$pos->z + $distance
		);

		$hasPlayer = false;
		$count = 0;
		foreach($this->getPosition()->getWorld()->getNearbyEntities($bb) as $e){
			if($e instanceof Player && !$e->isAFK()){
				$hasPlayer = true;
			}elseif($e::getNetworkTypeId() == LegacyEntityIdToStringIdMap::getInstance()->legacyToString($this->getEntityId())){
				$count++;
			}
		}
		if($hasPlayer && $count < 10){
			return true;
		}
		return false;
	}

	public function doUpdate() : bool{
		if($this->closed){
			return false;
		}
		if($this->canUpdate()){
			$this->nextTick += 200;
			$pos = $this->getPosition()->add(mt_rand(-1, 1), 1, mt_rand(-1, 1));
			$class = self::LEVEL_ENTITY_CLASSES[$this->levelEntity === -1 ? $this->getSpawnerLevel() : $this->levelEntity];

			/** @var Mob $entity */
			$entity = new $class(new Location($pos->x, $pos->y, $pos->z, $this->getPosition()->getWorld(), 0, 0));
			$entity->addStackValue(mt_rand(2, 4));

			$near = $entity->getNearestEntity(10);
			if($near !== null){
				$near->addStackValue($entity->getStackValue());
				$entity->flagForDespawn();
			}else{
				$entity->spawnToAll();
			}
		}
		return true;
	}

}
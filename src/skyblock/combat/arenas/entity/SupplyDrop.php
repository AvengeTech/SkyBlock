<?php namespace skyblock\combat\arenas\entity;

use core\Core;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\entity\{
	Entity,
	EntitySizeInfo,
	Location
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\world\{
	World,
	ChunkLoader,
	format\Chunk
};

use skyblock\SkyBlockPlayer;
use skyblock\generators\block\OreGenerator;
use skyblock\enchantments\item\MaxBook;
use skyblock\crates\item\KeyNote;
use skyblock\enchantments\EnchantmentData;
use skyblock\SkyBlock;
use skyblock\techits\item\TechitNote;


class SupplyDrop extends Entity implements ChunkLoader{

	protected float $gravity = 0.02;
	protected float $drag = 0.1;

	protected function getInitialDragMultiplier(): float {
		return $this->drag;
	}

	protected function getInitialGravity(): float {
		return $this->gravity;
	}

	public int $aliveTicks = 0;

	public int $loaderId = 0;
	public int $lastChunkHash;
	public array $loadedChunks = [];

	public int $groundTicks = 0;
	public bool $parachute = true;

	public static function getNetworkTypeId() : string{
		return "game:supplydrop";
	}

	public function __construct(Location $location, ?CompoundTag $nbt = null){
		parent::__construct($location, $nbt);
		
		$this->loaderId = $this->getId();

		$this->setHealth(1);
		$this->setMaxHealth(1);
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	public function attack(EntityDamageEvent $source) : void{
		if($this->hasParachute()) return;

		if($source instanceof EntityDamageByEntityEvent){
			/** @var SkyBlockPlayer $player */
			$player = $source->getDamager();
			if($player instanceof Player){
				$player->getGameSession()->getCombat()->addSupplyDrop();

				Core::announceToSS(TF::RED . TF::BOLD . ">>> " . TF::RESET . TF::YELLOW . "Supply Drop has been opened by " . TF::AQUA . $player->getName() . TF::YELLOW . " in warzone!");
			}
		}
		parent::attack($source);
	}

	protected function onDeath() : void{
		foreach($this->getRandomItems() as $item){
			$this->getPosition()->getWorld()->dropItem($this->getPosition(), $item);
		}
	}

	/** @return Item[] */
	public function getRandomItems() : array{
		$common = [
			ItemRegistry::TECHIT_NOTE()->setup(
				"Supply Drop", 
				((round(lcg_value() * 100, 2) <= 65.5 ? mt_rand(1, 5) : mt_rand(6, 10)) * 5000)
			)->init(),
			ItemRegistry::TECHIT_NOTE()->setup(
				"Supply Drop",
				((round(lcg_value() * 100, 2) <= 65.5 ? mt_rand(1, 5) : mt_rand(6, 10)) * 5000)
			)->init(),
			ItemRegistry::TECHIT_NOTE()->setup(
				"Supply Drop",
				((round(lcg_value() * 100, 2) <= 65.5 ? mt_rand(1, 5) : mt_rand(6, 10)) * 5000)
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"iron",
				((round(lcg_value() * 100, 2) <= 75.5 ? mt_rand(3, 6) : mt_rand(7, 9)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"iron",
				((round(lcg_value() * 100, 2) <= 75.5 ? mt_rand(3, 6) : mt_rand(7, 9)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"iron",
				((round(lcg_value() * 100, 2) <= 75.5 ? mt_rand(3, 6) : mt_rand(7, 9)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"iron",
				((round(lcg_value() * 100, 2) <= 75.5 ? mt_rand(3, 6) : mt_rand(7, 9)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"gold",
				((round(lcg_value() * 100, 2) <= 85.5 ? mt_rand(3, 5) : mt_rand(5, 7)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"gold",
				((round(lcg_value() * 100, 2) <= 85.5 ? mt_rand(3, 5) : mt_rand(5, 7)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"gold",
				((round(lcg_value() * 100, 2) <= 85.5 ? mt_rand(3, 5) : mt_rand(5, 7)))
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_COMMON,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_COMMON,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_COMMON,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_COMMON,
				EnchantmentData::CAT_SWORD,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_UNCOMMON,
				EnchantmentData::CAT_SWORD,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_UNCOMMON,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_UNCOMMON,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_UNCOMMON,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_UNCOMMON,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::POUCH_OF_ESSENCE()->setup(
				"Supply Drop", 
				((round(lcg_value() * 100, 2) <= 65.5 ? mt_rand(1, 5) : mt_rand(6, 10)) * 500)
			)->init(),
		];
		$rare = [
			ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount((mt_rand(1, 5))),
			ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount((mt_rand(1, 5))),
			ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount((mt_rand(1, 5))),
			ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount((mt_rand(1, 5))),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"diamond",
				((round(lcg_value() * 100, 2) <= 85.5 ? mt_rand(2, 4) : mt_rand(4, 6)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"diamond",
				((round(lcg_value() * 100, 2) <= 85.5 ? mt_rand(2, 4) : mt_rand(4, 6)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"diamond",
				((round(lcg_value() * 100, 2) <= 85.5 ? mt_rand(2, 4) : mt_rand(4, 6)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"diamond",
				((round(lcg_value() * 100, 2) <= 85.5 ? mt_rand(2, 4) : mt_rand(4, 6)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"emerald",
				((round(lcg_value() * 100, 2) <= 95.5 ? 1 : mt_rand(1, 4)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"emerald",
				((round(lcg_value() * 100, 2) <= 95.5 ? 1 : mt_rand(1, 4)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"emerald",
				((round(lcg_value() * 100, 2) <= 95.5 ? 1 : mt_rand(1, 4)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"emerald",
				((round(lcg_value() * 100, 2) <= 95.5 ? 1 : mt_rand(1, 4)))
			)->init(),
			BlockRegistry::ORE_GENERATOR()->addData(
				BlockRegistry::ORE_GENERATOR()->asItem(),
				mt_rand(1, mt_rand(1, 25) === 1 ? 8 : 6),
				mt_rand(1, mt_rand(1, 20) === 5 ? 8 : 5),
				0
			)->setCount(mt_rand(1, mt_rand(1, 10) === 5 ? 8 : 3)),
			BlockRegistry::ORE_GENERATOR()->addData(
				BlockRegistry::ORE_GENERATOR()->asItem(),
				mt_rand(1, mt_rand(1, 25) === 1 ? 8 : 6),
				mt_rand(1, mt_rand(1, 20) === 5 ? 8 : 5),
				0
			)->setCount(mt_rand(1, mt_rand(1, 10) === 5 ? 8 : 3)),
			BlockRegistry::ORE_GENERATOR()->addData(
				BlockRegistry::ORE_GENERATOR()->asItem(),
				mt_rand(1, mt_rand(1, 25) === 1 ? 8 : 6),
				mt_rand(1, mt_rand(1, 20) === 5 ? 8 : 5),
				0
			)->setCount(mt_rand(1, mt_rand(1, 10) === 5 ? 8 : 3)),
			BlockRegistry::ORE_GENERATOR()->addData(
				BlockRegistry::ORE_GENERATOR()->asItem(),
				mt_rand(1, mt_rand(1, 25) === 1 ? 8 : 6),
				mt_rand(1, mt_rand(1, 20) === 5 ? 8 : 5),
				0
			)->setCount(mt_rand(1, mt_rand(1, 10) === 5 ? 8 : 3)),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_RARE,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_RARE,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_RARE,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_RARE,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_RARE,
				EnchantmentData::CAT_SWORD,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_LEGENDARY,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_LEGENDARY,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_LEGENDARY,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_LEGENDARY,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_LEGENDARY,
				EnchantmentData::CAT_SWORD,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_ARMOR,
				false
			)->init()->setCount((round(lcg_value() * 100, 2) <= 65 ? 1 : mt_rand(2, 3))),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_ARMOR,
				false
			)->init()->setCount((round(lcg_value() * 100, 2) <= 65 ? 1 : mt_rand(2, 3))),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_ARMOR,
				false
			)->init()->setCount((round(lcg_value() * 100, 2) <= 65 ? 1 : mt_rand(2, 3))),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_ARMOR,
				false
			)->init()->setCount((round(lcg_value() * 100, 2) <= 65 ? 1 : mt_rand(2, 3))),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_SWORD,
				false
			)->init()->setCount((round(lcg_value() * 100, 2) <= 65 ? 1 : mt_rand(2, 3))),
		];
		$very_rare = [
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"emerald",
				((round(lcg_value() * 100, 2) <= 95.5 ? 2 : mt_rand(2, 5)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"emerald",
				((round(lcg_value() * 100, 2) <= 95.5 ? 2 : mt_rand(2, 5)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"emerald",
				((round(lcg_value() * 100, 2) <= 95.5 ? 2 : mt_rand(2, 5)))
			)->init(),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"emerald",
				((round(lcg_value() * 100, 2) <= 95.5 ? 2 : mt_rand(2, 5)))
			)->init(),
			BlockRegistry::ORE_GENERATOR()->addData(
				BlockRegistry::ORE_GENERATOR()->asItem(),
				mt_rand(1, mt_rand(1, 25) === 1 ? 8 : 7),
				mt_rand(1, mt_rand(1, 20) === 5 ? 8 : 6),
				0
			)->setCount(mt_rand(1, mt_rand(1, 10) === 5 ? 8 : 3)),
			BlockRegistry::ORE_GENERATOR()->addData(
				BlockRegistry::ORE_GENERATOR()->asItem(),
				mt_rand(1, mt_rand(1, 25) === 1 ? 8 : 7),
				mt_rand(1, mt_rand(1, 20) === 5 ? 8 : 6),
				0
			)->setCount(mt_rand(1, mt_rand(1, 10) === 5 ? 8 : 3)),
			BlockRegistry::ORE_GENERATOR()->addData(
				BlockRegistry::ORE_GENERATOR()->asItem(),
				mt_rand(1, mt_rand(1, 25) === 1 ? 8 : 7),
				mt_rand(1, mt_rand(1, 20) === 5 ? 8 : 6),
				0
			)->setCount(mt_rand(1, mt_rand(1, 10) === 5 ? 8 : 3)),
			BlockRegistry::ORE_GENERATOR()->addData(
				BlockRegistry::ORE_GENERATOR()->asItem(),
				mt_rand(1, mt_rand(1, 25) === 1 ? 8 : 7),
				mt_rand(1, mt_rand(1, 20) === 5 ? 8 : 6),
				0
			)->setCount(mt_rand(1, mt_rand(1, 10) === 5 ? 8 : 3)),
			ItemRegistry::SOLIDIFIER()->setup(
				(round(lcg_value() * 100, 2) <= 52 ? mt_rand(1, 3) : mt_rand(4, 5)),
				((round(lcg_value() * 100, 2) <= 52 ? mt_rand(1, 3) : mt_rand(4, 5)) * 250)
			)->init(),
			ItemRegistry::SOLIDIFIER()->setup(
				(round(lcg_value() * 100, 2) <= 52 ? mt_rand(1, 3) : mt_rand(4, 5)),
				((round(lcg_value() * 100, 2) <= 52 ? mt_rand(1, 3) : mt_rand(4, 5)) * 250)
			)->init(),
			ItemRegistry::SOLIDIFIER()->setup(
				(round(lcg_value() * 100, 2) <= 52 ? mt_rand(1, 3) : mt_rand(4, 5)),
				((round(lcg_value() * 100, 2) <= 52 ? mt_rand(1, 3) : mt_rand(4, 5)) * 250)
			)->init(),
			ItemRegistry::SOLIDIFIER()->setup(
				(round(lcg_value() * 100, 2) <= 52 ? mt_rand(1, 3) : mt_rand(4, 5)),
				((round(lcg_value() * 100, 2) <= 52 ? mt_rand(1, 3) : mt_rand(4, 5)) * 250)
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_ARMOR,
				true
			)->init()->setCount((round(lcg_value() * 100, 2) <= 75 ? 1 : mt_rand(1, 3))),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_ARMOR,
				true
			)->init()->setCount((round(lcg_value() * 100, 2) <= 75 ? 1 : mt_rand(1, 3))),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_ARMOR,
				true
			)->init()->setCount((round(lcg_value() * 100, 2) <= 75 ? 1 : mt_rand(1, 3))),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_ARMOR,
				true
			)->init()->setCount((round(lcg_value() * 100, 2) <= 75 ? 1 : mt_rand(1, 3))),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RANDOM_RARITY,
				-1,
				EnchantmentData::CAT_SWORD,
				true
			)->init()->setCount((round(lcg_value() * 100, 2) <= 75 ? 1 : mt_rand(1, 3))),
			ItemRegistry::KEY_NOTE()->setup(
				"Supply Drop",
				"divine",
				(round(lcg_value() * 100, 2) <= 2.35 ? mt_rand(1, 3) : 1)
			)->init(),
		];
		$extremely_rare = [
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_DIVINE,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_DIVINE,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_DIVINE,
				EnchantmentData::CAT_ARMOR,
				false
			)->init(),
			ItemRegistry::MAX_BOOK()->setup(
				MaxBook::TYPE_MAX_RARITY,
				EnchantmentData::RARITY_DIVINE,
				EnchantmentData::CAT_SWORD,
				false
			)->init(),
			BlockRegistry::PET_BOX()->addData(
				BlockRegistry::PET_BOX()->asItem()
			),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(
				EnchantmentData::RARITY_DIVINE
			)->init()->setCount((round(lcg_value() * 100, 2) <= 65 ? 1 : mt_rand(2, 3))),
			ItemRegistry::GUMMY_ORB()->setup(
				(round(lcg_value() * 100, 2) <= 55 ? mt_rand(1, 3) : mt_rand(4, 5))
			)->init()->setCount((round(lcg_value() * 100, 2) <= 90 ? mt_rand(1, 2) : mt_rand(3, 5))),
			ItemRegistry::ENERGY_BOOSTER()->setup(
				(round(lcg_value() * 100, 2) <= 55 ? mt_rand(1, 3) : mt_rand(4, 5))
			)->init()->setCount((round(lcg_value() * 100, 2) <= 90 ? mt_rand(1, 2) : mt_rand(3, 5))),
		];

		$items = [];
		$maxCommon = 3;
		$maxRare = 2;
		$maxVeryRare = 1;
		$maxExtremelyRare = 1;

		for ($i = 0; $i <= 10; $i++) {
			if($maxCommon <= 0) break;

			if (round(lcg_value() * 100, 2) > 54.55) continue;

			$items[] = $common[array_rand($common)];

			$maxCommon--;
		}

		for ($i = 0; $i <= 6; $i++) {
			if($maxRare <= 0) break;
			if (round(lcg_value() * 100, 2) > 34.35) continue;

			$items[] = $rare[array_rand($rare)];

			$maxRare--;
		}

		for ($i = 0; $i <= 4; $i++) {
			if($maxVeryRare <= 0) break;
			if (round(lcg_value() * 100, 2) > 11.65) continue;

			$items[] = $very_rare[array_rand($very_rare)];

			$maxVeryRare--;
		}

		for ($i = 0; $i <= 2; $i++) {
			if($maxExtremelyRare <= 0) break;
			if (round(lcg_value() * 100, 2) > 3.65) continue;

			$items[] = $extremely_rare[array_rand($extremely_rare)];

			$maxExtremelyRare--;
		}
		
		return $items;
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->getWorld()->registerChunkLoader($this, $this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
		$this->lastChunkHash = World::chunkHash($this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->lastChunkHash !== ($hash = World::chunkHash($x = $this->getPosition()->getFloorX() >> 4, $z = $this->getPosition()->getFloorZ() >> 4))){
			$this->registerToChunk($x, $z);

			World::getXZ($this->lastChunkHash, $oldX, $oldZ);
			$this->unregisterFromChunk($oldX, $oldZ);

			$this->lastChunkHash = $hash;
		}

		if($this->onGround && $this->hasParachute() && ++$this->groundTicks >= 5){
			$this->setParachute();

			$drop_manager = SkyBlock::getInstance()->getCombat()->getArenas()->getArena()->getDropManager();

			for($i = 0; $i < mt_rand(5, 9); $i++){
				$drop_manager->spawnMob($this->getLocation(), 3);
			}
		}

		return parent::entityBaseTick($tickDiff);
	}

	public function hasParachute() : bool{
		return $this->parachute;
	}

	public function setParachute(bool $spawned = false) : void{
		$this->parachute = $spawned;
		$this->getNetworkProperties()->setInt(EntityMetadataProperties::VARIANT, $spawned ? 0 : 1);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(2, 2, 2);
	}

	public function registerToChunk(int $chunkX, int $chunkZ){
		if(!isset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)])){
			$this->loadedChunks[World::chunkHash($chunkX, $chunkZ)] = true;
			$this->getWorld()->registerChunkLoader($this, $chunkX, $chunkZ);
		}
	}

	public function unregisterFromChunk(int $chunkX, int $chunkZ){
		if(isset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)])){
			unset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)]);
			$this->getWorld()->unregisterChunkLoader($this, $chunkX, $chunkZ);
		}
	}

	public function onChunkChanged(Chunk $chunk){

	}

	public function onChunkLoaded(Chunk $chunk){

	}

	public function onChunkUnloaded(Chunk $chunk){

	}

	public function onChunkPopulated(Chunk $chunk){

	}

	public function onBlockChanged(Vector3 $block){

	}

	public function getLoaderId() : int{
		return $this->loaderId;
	}

	public function isLoaderActive() : bool{
		return !$this->isFlaggedForDespawn() && !$this->closed;
	}

}
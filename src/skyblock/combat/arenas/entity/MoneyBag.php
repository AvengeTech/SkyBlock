<?php namespace skyblock\combat\arenas\entity;

use pocketmine\entity\{
	Entity,
	EntitySizeInfo,
	Location
};
use pocketmine\event\entity\{
	EntityDamageByEntityEvent
};
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\world\{
	World,
	ChunkLoader,
	format\Chunk,
	sound\PopSound
};

use core\utils\TextFormat;

use skyblock\SkyBlockPlayer;

class MoneyBag extends Entity implements ChunkLoader{

	protected float $gravity = 0.15;
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

	public int $totalTicks = 0;

	public static function getNetworkTypeId() : string{
		return "game:moneybag";
	}

	public function __construct(Location $location, ?CompoundTag $nbt = null, public int $worth = -1, public int $type = -1, float $scale = 1.0){
		parent::__construct($location, $nbt);
		
		if($worth == -1){
			$this->worth = mt_rand(2500, mt_rand(1, 5) === 5 ? 20000 : (mt_rand(1, 20) === 1 ? 100000 : 5000));
		}
		if($type == -1){
			$this->type = $worth > 25000 ? 1 : 0;
		}
		$this->getNetworkProperties()->setInt(EntityMetadataProperties::VARIANT, $type);
		
		$this->setScale($scale);
		
		$this->loaderId = $this->getId();

		$this->setHealth(1);
		$this->setMaxHealth(1);
	}

	public function getWorth() : int{
		return $this->worth;
	}

	public function getType() : int{
		return $this->type;
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->getWorld()->registerChunkLoader($this, $this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
		$this->lastChunkHash = World::chunkHash($this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
	}

	protected function onDeath() : void{
		$e = $this->getLastDamageCause();
		if($e instanceof EntityDamageByEntityEvent){
			$dmg = $e->getDamager();
			if($dmg instanceof Player){
				/** @var SkyBlockPlayer $dmg */
				$dmg->addTechits($worth = $this->getWorth());
				$dmg->sendMessage(TextFormat::GI . "You found " . TextFormat::AQUA . number_format($worth) . " techits!");
				$this->getPosition()->getWorld()->addSound($this->getPosition(), new PopSound());
			}
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->lastChunkHash !== ($hash = World::chunkHash($x = $this->getPosition()->getFloorX() >> 4, $z = $this->getPosition()->getFloorZ() >> 4))){
			$this->registerToChunk($x, $z);

			World::getXZ($this->lastChunkHash, $oldX, $oldZ);
			$this->unregisterFromChunk($oldX, $oldZ);

			$this->lastChunkHash = $hash;
		}

		if(++$this->totalTicks % 200 === 0){
			$this->jump();
		}

		return parent::entityBaseTick($tickDiff);
	}

	public function jump() : void{
		$this->getPosition()->getWorld()->addSound($this->getPosition(), new PopSound());
		if($this->onGround){
			$this->motion = $this->motion->withComponents(null, 0.75, null); //Y motion should already be 0 if we're jumping from the ground.
		}
	}

	public function getBagType() : int{
		return $this->type;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.7, 0.7, 0.7);
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
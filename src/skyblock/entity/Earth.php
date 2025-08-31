<?php namespace skyblock\entity;

use pocketmine\Server;
use pocketmine\entity\{
	Entity,
	EntitySizeInfo,
	Location
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\player\Player;
use pocketmine\world\{
	World,
	ChunkLoader,
	format\Chunk,
	sound\PopSound
};

use skyblock\SkyBlock;
use skyblock\islands\ui\IslandsUi;
use skyblock\islands\ui\manage\IslandInfoUi;
use skyblock\SkyBlockPlayer;

use core\Core;
use core\utils\TextFormat;

class Earth extends Entity implements ChunkLoader{
	
	public int $aliveTicks = 0;

	public array $lastPop = [];

	public int $loaderId = 0;
	public int $lastChunkHash;
	public array $loadedChunks = [];

	protected function getInitialDragMultiplier(): float
	{
		return 0;
	}

	protected function getInitialGravity(): float
	{
		return 0;
	}

	public function __construct(Location $loc, ?CompoundTag $nbt, float $scale){
		parent::__construct($loc, $nbt);
		$this->loaderId = $this->getId();

		$this->setNametag(TextFormat::AQUA . TextFormat::BOLD . "ISLANDS" . PHP_EOL . TextFormat::YELLOW . "(0 players)");
		$this->setNametagAlwaysVisible(true);
		$this->setScale($scale);
	}

	public static function getNetworkTypeId() : string{
		return "skyblock:earth";
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->lastChunkHash !== ($hash = World::chunkHash($x = (int) $this->getPosition()->x >> 4, $z = (int) $this->getPosition()->z >> 4))){
			$this->getWorld()->registerChunkLoader($this, $x, $z);

			World::getXZ($this->lastChunkHash, $oldX, $oldZ);
			$this->getWorld()->unregisterChunkLoader($this, $oldX, $oldZ);

			$this->lastChunkHash = $hash;
		}

		$this->aliveTicks++;
		if($this->aliveTicks % 100 === 0){
			$total = 0;
			//todo: multi server island support
			if(SkyBlock::getInstance()->getIslands()->getIslandManager()->loadIslandsOnMain()){
				foreach(Server::getInstance()->getOnlinePlayers() as $player){
					/** @var SkyBlockPlayer $player */
					if($player->isLoaded() && $player->getGameSession()->getIslands()->atIsland())
						$total++;
				}
			}else{
				foreach(Core::thisServer()->getSubServers(false, false) as $sub){
					if(str_starts_with($sub->getSubId(), "is")){
						$total += count($sub->getCluster()->getPlayers());
					}
				}
			}
			$this->setNametag(TextFormat::AQUA . TextFormat::BOLD . "ISLANDS" . PHP_EOL . TextFormat::YELLOW . "(" . $total . " players)");
		}
		
		$changedProperties = $this->getDirtyNetworkData();
		if(count($changedProperties) > 0){
			$this->sendData(null, $changedProperties);
			$this->getNetworkProperties()->clearDirtyProperties();
		}

		return $this->isAlive();
	}

	public function onCollideWithPlayer(Player $player) : void{
		/** @var SkyBlockPlayer $player */
		$this->pop($player);
		$player->setMotion($player->getPosition()->subtractVector($this->getPosition())->add(0, 1, 0)->normalize()->multiply(0.4));
		$player->showModal(new IslandsUi($player));
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
		if($source instanceof EntityDamageByEntityEvent){
			$player = $source->getDamager();
			if($player instanceof Player){
				$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->getPosition()->getWorld()->getDisplayName());
				if($island !== null) {
					/** @var SkyBlockPlayer $player */
					$player->showModal(new IslandInfoUi($player, $island));
					return;
				}
			}
		}
	}

	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if(parent::teleport($pos, $yaw, $pitch)){
			$controller = "controller.animation.earth.general";
			$animation = "rotatenomoon";
			$packet = AnimateEntityPacket::create($animation, $animation, "", 0, $controller, 0, [$this->getId()]);
			$this->getWorld()->broadcastPacketToViewers($this->getPosition(), $packet);
			return true;
		}
		return false;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.2, 1.7, 1.2);
	}

	public function pop(Player $player){
		if(isset($this->lastPop[$player->getName()]) && microtime(true) - $this->lastPop[$player->getName()] < 0.5)
			return;

		$this->lastPop[$player->getName()] = microtime(true);
		$this->getPosition()->getWorld()->addSound($player->getPosition()->add(0, 1, 0), new PopSound(), [$player]);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->getWorld()->registerChunkLoader($this, (int) $this->getPosition()->x >> 4, (int) $this->getPosition()->z >> 4);
		$this->lastChunkHash = World::chunkHash((int) $this->getPosition()->x >> 4, (int) $this->getPosition()->z >> 4);
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
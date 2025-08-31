<?php namespace skyblock\entity;

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

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\shop\ui\ShopUi;

use core\utils\TextFormat;

class DollarSign extends Entity implements ChunkLoader{

	public int $aliveTicks = 0;

	public array $lastPop = [];

	public int $loaderId = 0;
	public int $lastChunkHash;
	public array $loadedChunks = [];

	protected function getInitialDragMultiplier(): float {
		return 0;
	}

	protected function getInitialGravity(): float {
		return 0;
	}

	public function __construct(Location $loc, ?CompoundTag $nbt = null, float $scale = 1.0){
		parent::__construct($loc, $nbt);
		$this->loaderId = $this->getId();

		$this->setNametag(TextFormat::GREEN . TextFormat::BOLD . "SHOP");
		$this->setNametagAlwaysVisible(true);
		$this->setScale($scale);
	}

	public static function getNetworkTypeId() : string{
		return "game:dollarsign";
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

		return $this->isAlive();
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
		if($source instanceof EntityDamageByEntityEvent){
			$player = $source->getDamager();
			/** @var SkyBlockPlayer $player */
			if($player instanceof Player && $player->isLoaded()){
				$isession = $player->getGameSession()->getIslands();
				$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();
				if($island === null){
					$player->sendMessage(TextFormat::RI . "You can only use the island shop for the last island you visited!");
					return;
				}
				$perm = $island->getPermissions()->getPermissionsBy($player);
				if($perm === null || !$perm->getPermission(Permissions::USE_SHOP)){
					$player->sendMessage(TextFormat::RI . "You do not have permission to use this island's shop!");
					return;
				}
				$player->showModal(new ShopUi($player));
			}
		}
	}

	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if(parent::teleport($pos, $yaw, $pitch)){
			$controller = "controller.animation.mallet.general";
			$animation = "bounce";
			$packet = AnimateEntityPacket::create($animation, $animation, "", 0, $controller, 0, [$this->getId()]);
			$this->getWorld()->broadcastPacketToViewers($this->getPosition(), $packet);
			return true;
		}
		return false;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1, 1, 1);
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
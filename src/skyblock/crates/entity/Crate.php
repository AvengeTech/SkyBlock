<?php namespace skyblock\crates\entity;

use pocketmine\entity\{
	Entity,
	EntitySizeInfo,
	Location
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\{
	World,
	ChunkLoader,
	format\Chunk,
	sound\PopSound
};

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\crates\event\CrateWinEvent;
use skyblock\crates\prize\Prize;
use skyblock\crates\ui\OpenCrateUi;

use core\Core;
use core\network\Links;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\conversion\LegacyItemIds;
use core\utils\GenericSound;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use skyblock\crates\ui\MultiCrateResultUi;

abstract class Crate extends Entity implements ChunkLoader{

	const TICK_NONE = 0;
	const TICK_SPIN = 1;
	const TICK_OPEN = 2;

	public int $startingYaw;

	public int $tickType = self::TICK_NONE;
	public int $ticks = 0;
	
	public int $jumps = 0;
	public int $lastJump = -1;

	public ?Player $player = null;
	public ?Prize $prize = null;

	protected function getInitialDragMultiplier(): float
	{
		return 0;
	}

	protected function getInitialGravity(): float
	{
		return 0.1;
	}

	public int $loaderId = 0;
	public int $lastChunkHash;
	public array $loadedChunks = [];

	public function __construct(Location $location, ?CompoundTag $nbt = null){
		parent::__construct($location, $nbt);
		$this->startingYaw = $location->getYaw();

		$this->setNametagAlwaysVisible(true);
		$this->formatNametag();
		
		$this->getNetworkProperties()->setInt(EntityMetadataProperties::VARIANT, $this->getTypeVariant());

		$this->loaderId = $this->getId();
	}

	abstract public function getNameFormat() : string;

	abstract public function getTypeVariant() : int;

	abstract public function getType() : string;

	abstract public function getRandomRarity() : int;

	public function getNoKeyMessage() : string{
		return TextFormat::RI . "You don't have any " . $this->getType() . " keys to open! Find them by mining cobblestone and ores, or buy them at " . TextFormat::YELLOW . Links::SHOP;
	}
	
	public function formatNametag() : void{
		switch($this->getTickType()){
			case self::TICK_NONE:
				$this->setNametag($this->getNameFormat() . PHP_EOL . TextFormat::RESET . TextFormat::GRAY . "Tap to open!");
				break;
			case self::TICK_SPIN:
				$this->setNametag("");
				break;
			case self::TICK_OPEN:
				$this->setNametag(TextFormat::YELLOW . "You won" . PHP_EOL . $this->getPrize()->getName());
				break;
		}
	}

	public function getTickType() : int{
		return $this->tickType;
	}

	public function setTickType(int $type) : void{
		$this->tickType = $type;
		$this->ticks = 0;
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}
	
	public function getPrize() : ?Prize{
		return $this->prize;
	}

	public function start(Player $player) : void {
		/** @var SkyBlockPlayer $player */
		$this->player = $player;
		$player->getGameSession()->getCrates()->setOpening($this->getId());

		$this->setTickType(self::TICK_SPIN);
		$this->formatNametag();

		$this->animate("shaking_vigorously");
		
		$this->prize = SkyBlock::getInstance()->getCrates()->getRandomPrize($this->getRandomRarity());
		$this->getWorld()->addSound($this->getPosition(), new GenericSound($this->getPosition(), 89));
	}

	public function open() : void{
		$this->setTickType(self::TICK_OPEN);
		$prize = $this->prize;
		$this->openChest();
		$this->setNametag("");

		/** @var SkyBlockPlayer $player */
		$player = $this->getPlayer();

		$ev = new CrateWinEvent($this, $player, $prize);
		$ev->call();

		$prize->give($player);

		$session = $player->getGameSession()->getCrates();
		$session->takeKeys($this->getType());
		$session->addOpened();
	}

	public function openMultiple(Player $player, int $count = 1, bool $fromRemote = false): void {
		/** @var SkyBlockPlayer $player */
		$prizes = [];
		for($i = 1; $i <= $count; $i++){
			$prizes[] = SkyBlock::getInstance()->getCrates()->getRandomPrize($this->getRandomRarity());
		}
		$prizestr = "";
		foreach($prizes as $prize){
			$ev = new CrateWinEvent($this, $player, $prize);
			$ev->call();

			$prize->give($player);
			$prizestr .= $prize->getName() . "\n";
		}

		$form = new MultiCrateResultUi("Crate opening results", [
			"You used a total of " . TextFormat::YELLOW . $count . " " . $this->getType() . " keys " . TextFormat::WHITE . "and received the following:",
			$prizestr
		], $this, $fromRemote);
		$player->showModal($form);

		$session = $player->getGameSession()->getCrates();
		$session->takeKeys($this->getType(), $count);
		$session->addOpened($count);
	}

	public function done() : void{
		$this->setTickType(self::TICK_NONE);
		$this->formatNametag();

		$player = $this->getPlayer();
		/** @var SkyBlockPlayer $player */
		if($player instanceof Player && $player->isConnected() && $player->isLoaded()){
			$session = $player->getGameSession()->getCrates();
			$session->setOpening();
		}
		
		$this->player = null;
		$this->prize = null;
	}

	public function openFast(Player $player) : void{
		$this->player = $player;

		$this->setTickType(self::TICK_OPEN);
		$this->openChest();

		$prize = $this->prize = SkyBlock::getInstance()->getCrates()->getRandomPrize($this->getRandomRarity());

		$this->setNametag("");

		$ev = new CrateWinEvent($this, $player, $prize);
		$ev->call();

		$prize->give($player);
		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession()->getCrates();
		$session->setOpening($this->getId());
		$session->takeKeys($this->getType());
		$session->addOpened();
	}

	public function shakeLightly() : void{
		$this->animate("shaking_lightly");
	}

	public function openChest() : void{
		$this->animate("opening");
		$this->getWorld()->addSound($this->getPosition(), new GenericSound($this->getPosition(), 71));
	}

	public function animate(string $animation) : void{
		$controller = "controller.animation.crate.general";
		$packet = AnimateEntityPacket::create($animation, $animation, "", 0, $controller, 0, [$this->getId()]);
		$this->getWorld()->broadcastPacketToViewers($this->getPosition(), $packet);
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
		if($source instanceof EntityDamageByEntityEvent){
			$player = $source->getDamager();

			if($this->getTickType() == self::TICK_NONE){
				$this->shakeLightly();
			}

			/** @var SkyBlockPlayer $player */
			$session = $player->getGameSession()->getCrates();
			if($session->isOpening()){
				$player->sendMessage(TextFormat::RI . "You can only open one crate at a time!");
				return;
			}
			if($session->getKeys($this->getType()) <= 0){
				$player->sendMessage($this->getNoKeyMessage());
				return;
			}
			if(!$player->getInventory()->canAddItem(ItemRegistry::getItemById(LegacyItemIds::legacyIdToTypeId(255, 0), -1, 1))){
				$player->sendMessage(TextFormat::RI . "Your inventory is full! Please empty your inventory before using this.");
				return;
			}

			$player->showModal(new OpenCrateUi($player, $this));
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->lastChunkHash !== ($hash = World::chunkHash($x = $this->getPosition()->getFloorX() >> 4, $z = $this->getPosition()->getFloorZ() >> 4))){
			$this->registerToChunk($x, $z);

			World::getXZ($this->lastChunkHash, $oldX, $oldZ);
			$this->unregisterFromChunk($oldX, $oldZ);

			$this->lastChunkHash = $hash;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(($type = $this->getTickType()) !== self::TICK_NONE){
			if(($player = $this->getPlayer()) === null || !$player->isConnected()){
				$this->done();
				return true;
			}
			$this->ticks++;

			switch($type){
				case self::TICK_SPIN:
					if($this->jumps >= 3){
						if($this->onGround){
							//$this->setRotation($this->startingYaw, 0);
							$this->jumps = 0;
							$this->lastJump = -1;
							$this->open();
						}else{
							//$this->setRotation($this->getLocation()->getYaw() + 45, 0);
						}
					}else{
						$this->lastJump--;
						if($this->onGround){
							//$this->setRotation($this->startingYaw, 0);
							if($this->lastJump <= 0){
								$this->jump();
							}
						}else{
							//$this->setRotation($this->getLocation()->getYaw() + 45, 0);
						}
					}
					break;
				case self::TICK_OPEN:
					if($this->ticks == 10){
						$this->formatNametag();
					}
					if($this->ticks >= 55){
						$this->done();
					}
					break;
			}
		}

		return true;
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->getWorld()->registerChunkLoader($this, $this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
		$this->lastChunkHash = World::chunkHash($this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1, 0.8, 1);
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

	public function jump() : void{
		$this->setMotion(new Vector3(0, 0.6, 0));
		$this->getWorld()->addSound($this->getPosition(), new PopSound());
		$this->jumps++;
		$notes = [0, 7, 12];
		$this->getWorld()->addSound($this->getPosition(), new GenericSound($this->getPosition(), 81, 2, $notes[$this->jumps - 1] ?? 0));
		$this->lastJump = 20;
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	public static function getNetworkTypeId() : string{
		return "game:crate";
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
<?php namespace skyblock\islands\entity;

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

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\ui\manage\IslandInfoUi;

use core\utils\TextFormat;

class IslandEntity extends Entity{

	public int $aliveTicks = 0;

	public ?Island $island = null;

	protected function getInitialDragMultiplier(): float
	{
		return 0;
	}

	protected function getInitialGravity(): float
	{
		return 0;
	}

	public function __construct(Location $loc, ?CompoundTag $nbt = null){
		parent::__construct($loc, $nbt);
		$this->setNametag(TextFormat::YELLOW . "Island Menu" . PHP_EOL . TextFormat::GRAY . "(Tap me!)");

		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($loc->getWorld()->getDisplayName());
		if($island !== null){
			$this->island = $island;
			if($island->getIslandEntity() === null || $island->getIslandEntity() === $this){
				$island->setIslandEntity($this);
			}else{
				$this->flagForDespawn();
			}
		}
	}

	public function getIsland() : ?Island{
		return $this->island;
	}

	public static function getNetworkTypeId() : string{
		return "skyblock:island";
	}

	public function canSaveWithChunk() : bool{
		return true;
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
		if($source instanceof EntityDamageByEntityEvent){
			$player = $source->getDamager();
			if($player instanceof Player){
				/** @var SkyBlockPlayer $player */
				$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->getPosition()->getWorld()->getDisplayName());
				if($island !== null){
					$player->showModal(new IslandInfoUi($player, $island));
					return;
				}
			}
		}
	}

	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if(parent::teleport($pos, $yaw, $pitch)){
			$controller = "controller.animation.island.general";
			$animation = "spinny";
			$packet = AnimateEntityPacket::create($animation, $animation, "", 0, $controller, 0, [$this->getId()]);
			$this->getWorld()->broadcastPacketToViewers($this->getPosition(), $packet);
			return true;
		}
		return false;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.8, 1, 0.8);
	}

}
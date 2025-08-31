<?php namespace skyblock\combat\arenas\entity;

use pocketmine\entity\{Entity, EntitySizeInfo, Location};
use pocketmine\math\Math;

use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\{network\mcpe\protocol\types\entity\EntityIds, player\Player};
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\utils\TextFormat;

use skyblock\spawners\entity\{
	Mob, Passive
};
use skyblock\spawners\entity\passive\Pig;
use skyblock\spawners\event\SpawnerKillEvent;

class Turret extends Mob{

	public $enabled = true;

	public function __construct(Location $loc){
		parent::__construct($loc);
		$this->setNametagVisible(true);
		$this->setNameTagAlwaysVisible(true);
		$this->setNametag(TextFormat::RED . TextFormat::BOLD . $this->getName() . TextFormat::YELLOW . " (" . $this->getStackValue() . ")" . TextFormat::RESET . TextFormat::GREEN . " " . $this->getHealth() . "/" . $this->getMaxHealth());
	}

	public function getName() : string{
		return "Turret";
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	public function kill() : void{
		if($this->getStackValue() == 1){
			parent::kill();
		}else{
			$this->onDeath();
			$this->subStackValue();
			$this->setHealth($this->getMaxHealth());
			$this->setNametag(($this instanceof Passive ? TextFormat::AQUA : TextFormat::RED) . TextFormat::BOLD . $this->getName() . TextFormat::YELLOW . " (" . $this->getStackValue() . ")" . TextFormat::RESET . TextFormat::GREEN . " " . $this->getHealth() . "/" . $this->getMaxHealth());
		}
		$cause = $this->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent){
			$player = $cause->getDamager();
			if($player instanceof Player){
				$ev = new SpawnerKillEvent($this, $player);
				$ev->call();
			}
		}
	}

	public function onDeath() : void{
		$this->disable();
		
		$last = $this->getLastDamageCause();
		if($last instanceof EntityDamageByEntityEvent){
			$damager = $last->getDamager();
			if($damager instanceof Player){

			}
		}
	}

	public function disable() : bool{
		return $this->setEnabled(false);
	}

	public function enable() : bool{
		return $this->setEnabled();
	}

	public function setEnabled(bool $enable = true) : bool{
		$enabled = $this->isEnabled();
		$this->enabled = $enable;
		return $enabled == $enable;
	}

	public function isEnabled() : bool{
		return $this->enabled;
	}

	public function onDeathUpdate(int $tickDiff) : bool{
		return false;
	}

	public function getNearestPlayer(float $maxDistance) : ?Player{
		$pos = $this->getPosition();

		$minX = Math::floorFloat(($pos->x - $maxDistance) / 16);
		$maxX = Math::ceilFloat(($pos->x + $maxDistance) / 16);
		$minZ = Math::floorFloat(($pos->z - $maxDistance) / 16);
		$maxZ = Math::ceilFloat(($pos->z + $maxDistance) / 16);
		$currentTargetDistSq = $maxDistance ** 2;
		/** @var Entity|null $currentTarget */
		$currentTarget = null;
		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				foreach($this->getWorld()->getChunkEntities($x, $z) as $entity){
					if(!($entity instanceof Player) or $entity->isClosed() or $entity->isFlaggedForDespawn() or !$entity->isAlive() or $entity === $this){
						continue;
					}
					$distSq = $entity->getPosition()->distanceSquared($pos);
					if($distSq < $currentTargetDistSq){
						$currentTargetDistSq = $distSq;
						$currentTarget = $entity;
					}
				}
			}
		}
		return $currentTarget;
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		if(!$this instanceof Pig){
			$this->sound("hurt" . mt_rand(1,2));
		}else{
			$this->sound("say" . mt_rand(1,2));
		}
		$this->setNametag(($this instanceof Passive ? TextFormat::AQUA : TextFormat::RED) . TextFormat::BOLD . $this->getName() . TextFormat::YELLOW . " (" . $this->getStackValue() . ")" . TextFormat::RESET . TextFormat::GREEN . " " . $this->getHealth() . "/" . $this->getMaxHealth());
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->ticksLived % 60 == 0){
			$this->shoot($this->getNearestPlayer(20));
		}

		return $this->isAlive();
	}

	public function getSoundPrefix() : string{
		return strtolower($this->getName());
	}

	public function sound(string $type) : void{
		$pk = new PlaySoundPacket();
		$pk->soundName = "mob" . $this->getSoundPrefix() . $type;
		$pk->x = (int) $this->getPosition()->getX();
		$pk->y = (int) $this->getPosition()->getY();
		$pk->z = (int) $this->getPosition()->getZ();
		$pk->volume = 50;
		$pk->pitch = 1;
		foreach($this->getViewers() as $player){
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	protected function getInitialSizeInfo(): EntitySizeInfo{
		return new EntitySizeInfo(2, 2);
	}

	public static function getNetworkTypeId(): string{
		return EntityIds::SLIME;
	}

	public function shoot(Player $player){

	}
}
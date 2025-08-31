<?php namespace skyblock\enchantments\effects\entities;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\world\{
	particle\SmokeParticle
};
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageEvent;

class CreepySnowGolem extends Living{

	public $width = 0.6;
	public $length = 0.6;
	public $height = 0.6;

	const LIFESPAN = 80; //ticks

	public $deathposition;

	public $aliveTicks = 0;
	public $target = null;

	public function __construct(Location $location, Vector3 $deathposition){
		parent::__construct($location);
		$this->deathposition = $deathposition;
		$this->teleport($deathposition);
		$this->setCanSaveWithChunk(false);
	}

	public function getName() : string{
		return "Creepy Snow Golem";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$this->aliveTicks++;

		if($this->aliveTicks > self::LIFESPAN && $this->isAlive()){ //buhbye
			for($i = 0; $i < 3; $i++)
				$this->getWorld()->addParticle($this->getPosition(), new SmokeParticle());

			$this->flagForDespawn();
		}else{
			$this->walk();
		}

		return $this->isAlive();
	}

	public function atTarget() : bool{
		return $this->getPosition()->distance($this->getTarget()) < 2;
	}

	public function hasTarget() : bool{
		return $this->getTarget() !== null;
	}

	public function getTarget() : ?Vector3{
		return $this->target;
	}

	public function findTarget() : void{
		$this->target = $this->getPosition()->add(mt_rand(-5, 5), 0, mt_rand(-5, 5));
	}

	public function walk() : void{
		if(!$this->hasTarget() || $this->atTarget()){
			$this->findTarget();
			return;
		}

		$x = $this->getTarget()->x - $this->getLocation()->x;
		$y = $this->getTarget()->y - $this->getLocation()->y;
		$z = $this->getTarget()->z - $this->getLocation()->z;

		$this->motion->x = $this->getSpeed() * 0.35 * ($x / (abs($x) + abs($z)));
		$this->motion->z = $this->getSpeed() * 0.35 * ($z / (abs($x) + abs($z)));
		$this->setRotation(rad2deg(atan2(-$x, $z)), 0);
	}

	public function getSpeed() : float{
		return 0.7;
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();

		for($i = 0; $i < 3; $i++)
			$this->getWorld()->addParticle($this->getPosition(), new SmokeParticle());

		//$this->getWorld()->addSound(new PlaySound($this, "mob.spider.death"));
		$this->flagForDespawn();
	}

	protected function getInitialSizeInfo(): EntitySizeInfo{
		return new EntitySizeInfo($this->height, $this->width);
	}

	public static function getNetworkTypeId(): string{
		return "minecraft:snow_golem";
	}
}
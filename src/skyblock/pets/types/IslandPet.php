<?php

namespace skyblock\pets\types;

use pocketmine\block\Air;
use pocketmine\block\Lava;
use pocketmine\block\Water;
use pocketmine\entity\Location;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;
use skyblock\pets\types\island\RabbitPet;

class IslandPet extends EntityPet{

	private int $jumpAttempts = 0;
	private float $lastJumpAttempt = 0;

	public function __construct(
		Location $location,
		?CompoundTag $nbt = null
	){
		parent::__construct($location, $nbt);

		$this->lastJumpAttempt = microtime(true);
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$hasUpdate) return false;

		if($this->jumpAttempts > 0 && microtime(true) - $this->lastJumpAttempt > 1.5) $this->jumpAttempts = 0;

		if(is_null($this->owner) && !($this->isFlaggedForDespawn() || $this->isClosed())){
			$this->flagForDespawn();
			return false;
		}

		if($this->getBoundingBox()->intersectsWith($this->getOwner()->getBoundingBox())){ // Collision
			$x = $this->getPosition()->x - $this->owner->getPosition()->x;
			$z = $this->getPosition()->z - $this->owner->getPosition()->z;

			$this->motion->x = $this->getMovementSpeed() * 0.35 * $x;
			$this->motion->z = $this->getMovementSpeed() * 0.35 * $z;
		}

		if($this->mode === self::MODE_FOLLOWING){
			if($this->getPosition()->distance($this->owner->getPosition()) > self::BLOCKS_TILL_TELEPORT && $this->owner->isOnGround()){
				$this->teleport($this->owner->getPosition());
			}else{
				$this->follow();
			}
		}else{
			if($this->getPosition()->distance($this->owner->getPosition()) <= 5){
				$x = $this->owner->getPosition()->x - $this->getPosition()->x;
				$z = $this->owner->getPosition()->z - $this->getPosition()->z;
				
				$this->setRotation(rad2deg(atan2(-$x, $z)), 0);
			}
		}

		if($this instanceof FlyingPet){
			if($this->mode === self::MODE_FOLLOWING){
				if($this->getPosition()->getY() < ($this->getOwner()?->getPosition()->getY() + ($this->getOwner()?->getEyeHeight() - 0.2))) $this->motion->y += 0.095;
			}else{
				if(!$this->getWorld()->getBlock($this->getPosition()->getSide(Facing::DOWN)) instanceof Air) $this->motion->y += 0.095;
			}
		}else{
			if(
				$this->getWorld()->getBlock($this->getPosition()) instanceof Water || 
				$this->getWorld()->getBlock($this->getPosition()) instanceof Lava
			) $this->motion->y += 0.085;
		}

		return true;
	}

	public function follow() : void{
		if(is_null($this->owner)) return;
		if(!$this->owner->isOnGround()) return;

		$x = $this->owner->getLocation()->x - $this->getPosition()->getX();
		$y = $this->owner->getLocation()->y - $this->getPosition()->getY();
		$z = $this->owner->getLocation()->z - $this->getPosition()->getZ();

		if(!$this instanceof FlyingPet && $this->isCollidedHorizontally){
			if(
				$this->getWorld()->getBlock($this->getPosition()) instanceof Water || 
				$this->getWorld()->getBlock($this->getPosition()) instanceof Lava
			){
				$this->motion->y += 0.09;
			}else{
				$this->jump();
				$this->checkObstruction($x, $y, $z);

				if($this->ticksLived % 10 === 0 && !$this->owner->isFlying()){
					$this->jumpAttempts++;
					$this->lastJumpAttempt = microtime(true);

					if($this->jumpAttempts >= 7){
						$this->jumpAttempts = 0;
						$this->teleport($this->owner->getPosition());
					}
				}
			}
		}

		if($x * $x + $z * $z < $this->getScale() * 2.5){
			$this->motion->x = 0;
			$this->motion->z = 0;
		}else{
			if($this instanceof RabbitPet && $this->isOnGround() && !$this->isCollidedHorizontally){
				$this->motion->y += 0.30;
			}

			$this->motion->x = $this->getMovementSpeed() * 0.50 * ($x / (abs($x) + abs($z)));
			$this->motion->z = $this->getMovementSpeed() * 0.50 * ($z / (abs($x) + abs($z)));
		}

		$this->setRotation(rad2deg(atan2(-$x, $z)), 0);
		$this->updateMovement();
	}
}
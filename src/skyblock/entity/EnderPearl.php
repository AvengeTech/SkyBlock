<?php namespace skyblock\entity;

use core\staff\anticheat\session\SessionManager;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\entity\projectile\EnderPearl as EP;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
use skyblock\SkyBlockPlayer;

class EnderPearl extends EP{

	protected function onHit(ProjectileHitEvent $event) : void{
		$owner = $this->getOwningEntity();
		/** @var SkyBlockPlayer $owner */
		if($owner !== null && $owner->isLoaded() && !$owner->getGameSession()->getParkour()->hasCourseAttempt()){
			//TODO: check end gateways (when they are added)
			//TODO: spawn endermites at origin

			$this->getWorld()->addParticle($origin = $owner->getPosition(), new EndermanTeleportParticle());
			$this->getWorld()->addSound($origin, new EndermanTeleportSound());
			$owner->teleport($target = $event->getRayTraceResult()->getHitVector());
			$this->getWorld()->addSound($target, new EndermanTeleportSound());

			$owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
		}
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

}
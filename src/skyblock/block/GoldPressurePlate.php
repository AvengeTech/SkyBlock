<?php namespace skyblock\block;

use core\staff\anticheat\session\SessionManager;
use pocketmine\block\WeightedPressurePlate;
use pocketmine\entity\Entity;
use pocketmine\math\AxisAlignedBB;
use skyblock\SkyBlockPlayer as Player;
use pocketmine\world\sound\{
	ClickSound,
	GhastShootSound
};


class GoldPressurePlate extends WeightedPressurePlate {

	protected function recalculateCollisionBoxes(): array {
		return [AxisAlignedBB::one()];
	}

	public function hasEntityCollision(): bool {
		return true;
	}

	public function onEntityInside(Entity $player): bool {
		if ($player instanceof Player && $player->isLoaded()) {
			if (!$player->canActivatePressurePlate()) return true;
			$player->setLastPressurePlateActivation();

			$motion = $player->getDirectionVector()->normalize()->multiply(2);
			$motion->y = 1.2;
			$player->setMotion($motion);

			if (!$player->isVanished()) {
				$this->click();
				$this->getPosition()->getWorld()->addSound($this->getPosition(), new GhastShootSound());
			}
		}
		return true;
	}

	public function click(): void {
		$this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickSound());
	}
}
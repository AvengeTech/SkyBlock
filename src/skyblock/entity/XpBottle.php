<?php namespace skyblock\entity;

use pocketmine\Server;
use pocketmine\entity\projectile\Throwable;
use pocketmine\entity\Entity;
use pocketmine\math\RayTraceResult;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\settings\SkyBlockSettings;

use core\utils\Utils;

class XpBottle extends Throwable{

	public int $ticks = 0;

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$this->ticks++;

		$hasUpdate = parent::entityBaseTick($tickDiff);
		if($this->ticks > 1200 or $this->isCollided){
			$player = $this->getOwningEntity();
			if($player instanceof Player){
				/** @var SkyBlockPlayer $player */
				$ss = $player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::AUTO_XP);
				if($ss){
					$player->getXpManager()->addXp(mt_rand(3, 11));
				}else{
					Utils::dropTempExperience($this->getWorld(), $this->getPosition(), mt_rand(3, 11));
				}
			}
			$this->flagForDespawn();
			$hasUpdate = true;
		}

		return $hasUpdate;
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$player = $this->getOwningEntity();
		if($player instanceof Player){
			/** @var SkyBlockPlayer $player */
			$ss = $player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::AUTO_XP);
			if($ss){
				$player->getXpManager()->addXp(mt_rand(3, 11));
			}else{
				Utils::dropTempExperience($this->getWorld(), $this->getPosition(), mt_rand(3, 11));
			}
		}
		$this->flagForDespawn();
	}

	public static function getNetworkTypeId() : string{
		return EntityIds::XP_BOTTLE;
	}

}
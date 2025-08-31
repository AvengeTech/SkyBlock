<?php namespace skyblock\parkour\block;

use pocketmine\block\WeightedPressurePlate;
use pocketmine\entity\Entity;
use pocketmine\math\AxisAlignedBB;
use pocketmine\world\sound\{
	ClickSound,
	XpLevelUpSound
};

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player
};

use core\utils\TextFormat;

class IronPressurePlate extends WeightedPressurePlate{

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()];
	}
	
	public function hasEntityCollision() : bool{
		return true;
	}
	
	public function onEntityInside(Entity $player) : bool{
		if($player instanceof Player && $player->isLoaded()){
			if(!$player->atSpawn()) return true;
			if(!$player->canActivatePressurePlate()) return true;
			$player->setLastPressurePlateActivation();
			
			$session = $player->getGameSession()->getParkour();
			if($session->hasCourseAttempt()){
				$attempt = $session->getCourseAttempt();
				if(($current = $attempt->getCurrentCheckpoint()) !== null && $current->equals($this->getPosition())){
					$attempt->addCurrentCheckpoint();
					$player->sendMessage(TextFormat::GI . "Reached checkpoint " . TextFormat::YELLOW . $attempt->getCurrentCheckpointId());

					$this->click();
				}elseif($current === null && $attempt->getCourse()->getEndPosition()->equals($this->getPosition())){
					$lastScore = $session->getCourseScore($attempt->getCourse())->getFastestTime();
					$shards = 0;
					$player->getSession()->getLootBoxes()->addShards($shards = mt_rand(5, 10));
					$player->sendMessage(TextFormat::GI . "You beat the parkour in " . TextFormat::YELLOW . $attempt->getTimeElapsed() . " seconds" . TextFormat::GRAY . " and earned " . TextFormat::AQUA . $shards . " shard" . ($shards > 1 ? "s" : "") . "!");
					if($attempt->complete($session)){
						$this->getPosition()->getWorld()->addSound($this->getPosition(), new XpLevelUpSound(5));
						$player->sendMessage(TextFormat::GI . "You beat your highscore of " . TextFormat::YELLOW . $lastScore . " seconds");
					}
					$session->setCourseAttempt();
					$this->click();
				}
			}else{
				foreach(SkyBlock::getInstance()->getParkour()->getCourses() as $course){
					//var_dump($course->getStartPosition());
					//var_dump($this->getPosition());
					if($course->getStartPosition()->equals($this->getPosition())){
						$cs = $player->getGameSession()->getCombat();
						if($cs->inPvPMode()){
							$player->sendMessage(TextFormat::RI . "You cannot start a parkour course while in PvP mode!");
						}else{
							$session->setCourseAttempt($course);
							$player->sendMessage(TextFormat::GI . "Started parkour course " . TextFormat::YELLOW . $course->getName() . TextFormat::GRAY . ", good luck! Type " . TextFormat::AQUA . "/spawn" . TextFormat::GRAY . " to exit course!");
						}
						$this->click();
						break;
					}
				}	
			}
		}
		return true;
	}
	
	public function click() : void{
		$this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickSound());
	}
}
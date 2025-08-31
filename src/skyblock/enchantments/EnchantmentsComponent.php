<?php

declare(strict_types=1);

namespace skyblock\enchantments;

use core\session\component\BaseComponent;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\particle\HeartParticle;

use skyblock\SkyBlock;

class EnchantmentsComponent extends BaseComponent{

	private bool $canAbsorb = true;
	private bool $isAbsorbing = false;
	private bool $canForesee = false;
	
	private float $absorbDamage = 0.0;
	private float $lastHitTime = 0.0;

	private int $foreseenHits = 0;

	public function getName() : string{
		return "enchantments";
	}

	public function getLastHit() : float{
		return $this->lastHitTime;
	}

	public function setLastHit() : self{
		$this->lastHitTime = microtime(true);

		return $this;
	}

	public function canAbsorb() : bool{
		return $this->canAbsorb;
	}

	public function blockAbsorb(bool $value) : self{
		$this->canAbsorb = $value;

		return $this;
	}

	public function isAbsorbing() : bool{
		return $this->isAbsorbing;
	}

	public function absorb(Player $player) : void{
		$this->isAbsorbing = true;

		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player){
			if(!$player->isOnline()) return;
			if(!$this->canAbsorb){
				$this->canAbsorb = true;
				return;
			}

			$this->isAbsorbing = false;
			$this->canAbsorb = true;

			$player->setHealth($player->getHealth() + ($this->getAbsorbDamage() * 0.5));

			$this->absorbDamage = 0.0;

			$player->getWorld()->broadcastPacketToViewers($player->getPosition(), PlaySoundPacket::create(
				"random.potion.brewed",
				$player->getPosition()->x,
				$player->getPosition()->y,
				$player->getPosition()->z,
				0.75,
				1.0
			));

			for($i = 0; $i < 10; $i++){
				$player->getWorld()->addParticle($player->getPosition()->add(mt_rand(-1, 1), mt_rand(-2, 2), mt_rand(-1, 1)), new HeartParticle(mt_rand(1, 3)));
			}
		}), 20 * 2);
	}

	public function addAbsorbDamage(float $damage) : self{
		$this->absorbDamage += $damage;

		return $this;
	}

	public function setAbsorbDamage(float $damage) : self{
		$this->absorbDamage = $damage;

		return $this;
	}

	public function getAbsorbDamage() : float{
		return $this->absorbDamage;
	}

	public function isForeseeing() : bool{
		return $this->canForesee;
	}

	public function canForesee(bool $value) : self{
		$this->canForesee = $value;

		return $this;
	}

	public function addForeseenHits(int $hits) : self{
		$this->foreseenHits += $hits;

		return $this;
	}

	public function setForeseenHits(int $hits) : self{
		$this->foreseenHits = $hits;

		return $this;
	}

	public function getHitsForeseen() : int{
		return $this->foreseenHits;
	}
}
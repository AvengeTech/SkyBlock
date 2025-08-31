<?php

namespace skyblock\pets\types;

use pocketmine\player\Player;
use skyblock\pets\event\PetGainXpEvent;
use skyblock\pets\event\PetLevelUpEvent;
use skyblock\pets\Structure;

class PetData{

	private ?int $lastEnergyUpdate = null;

	public function __construct(
		private int $identifier,
		private string $name,
		private int $level = 1,
		private int $xp = 0, 
		private float $energy = 10.0,
		private bool $resting = true,
		private ?int $originalRestTime = null
	){
		if(is_null($originalRestTime)) $this->originalRestTime = time();
	}

	public function getIdentifier() : int{ return $this->identifier; }

	public function getName() : string{ return $this->name; }

	public function setName(string $name) : self{
		$this->name = $name;
		return $this;
	}

	public function getDefaultName() : string{
		return Structure::PETS[$this->identifier][Structure::DATA_NAME];
	}

	public function getLevel() : int{ return $this->level; }

	public function setLevel(int $level) : self{
		$this->level = $level;
		return $this;
	}

	public function getMaxLevel() : int{ return Structure::PETS[$this->getIdentifier()][Structure::DATA_MAX_LEVEL]; }

	public function isMaxLevel() : bool{ return $this->level >= $this->getMaxLevel(); }

	public function getDescription(?int $level = null) : string{
		$level = floor((is_null($level) ? $this->level : $level) / 10) * 10;

		if($level < 1) $level = 1;
		if($level > 50) $level = 50;

		return Structure::PETS[$this->identifier][Structure::DATA_BUFFS][$level][Structure::DATA_BUFF_DESCRIPTION];
	}

	/**
	 * Gives the buff data like chance or multiplier.
	 *
	 * @return array
	 */
	public function getBuffData() : array{
		$level = floor($this->level / 10) * 10;

		if($level < 1) $level = 1;

		return Structure::PETS[$this->identifier][Structure::DATA_BUFFS][$level][Structure::DATA_BUFF_CHANCES];
	}

	public function getRarity() : int{ return Structure::PETS[$this->getIdentifier()][Structure::DATA_RARITY]; }

	public function getMaxEnergy() : int{ return Structure::MAX_ENERGY[$this->getRarity()]; }

	public function atMaxEnergy() : bool{ return $this->energy >= $this->getMaxEnergy(); }

	public function getXp() : int{ return $this->xp; }

	public function addXp(int $xp, ?Player $player = null) : self{
		$ev = new PetGainXpEvent($player, $this, $this->xp);
		$ev->call();

		return $this->setXp($this->getXp() + $xp, $player);
	}

	public function subXp(int $xp, ?Player $player = null) : self{ return $this->setXp($this->getXp() - $xp, $player); }

	public function setXp(int $xp, ?Player $player = null) : self{
		$this->xp = max(0, $xp);

		if($this->xp >= $this->getRequiredXp()){
			$oldLevel = $this->level;

			$this->level++;

			$ev = new PetLevelUpEvent($player, $this, $oldLevel, $this->level);
			$ev->call();

			$this->xp = ($this->isMaxLevel() ? 0 : $this->getRequiredXp() - $this->xp);
		}
		return $this;
	}

	public function getRequiredXp() : int{
		$level = floor($this->level / 10) * 10;

		if($level < 1) $level = 1;

		$baseXpPerEveryTen = match($level){
			1 => 750,
			10 => 2500,
			20 => 5000,
			30 => 10000,
			40 => 15000,
			50 => 20000, // not going to be used since max level is 50.
			default => 20000
		};

		return $baseXpPerEveryTen + ($this->level * 250);
	}

	public function getEnergy() : float{ return round($this->energy, 2); }

	public function addEnergy(float $energy) : self{return $this->setEnergy($this->getEnergy() + $energy); }

	public function subEnergy(float $energy) : self{ return $this->setEnergy($this->getEnergy() - $energy); }

	public function setEnergy(float $energy) : self{
		$this->energy = max(0.0, min($this->getMaxEnergy(), $energy));
		return $this;
	}

	public function rest(bool $value = true) : self{
		$this->resting = $value;
		$this->originalRestTime = ($value ? time() : null);

		if(!$value) $this->lastEnergyUpdate = null;

		return $this;
	}

	public function isResting() : bool{ return $this->resting; }

	public function getOriginalRestTime() : int{ return $this->originalRestTime; }

	public function getLastEnergyUpdate() : int{ return $this->lastEnergyUpdate; }

	public function updateRestEnergy() : self{ // idk if I should make it return a bool, just have it as self for now.
		if($this->lastEnergyUpdate === time()) return $this;
		if($this->energy >= $this->getMaxEnergy()) return $this;

		$regainPerMinute = Structure::ENERGY_REGAIN[$this->getRarity()];
		$time = (is_null($this->lastEnergyUpdate) ? $this->originalRestTime : $this->lastEnergyUpdate);

		$minutes = (time() - $time) / 60;

		if($minutes < 1) return $this;

		$this->lastEnergyUpdate = time();

		$energy = $regainPerMinute * $minutes;

		$this->addEnergy($energy);

		return $this;
	}
}
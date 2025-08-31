<?php namespace skyblock\combat;

use core\user\User;
use core\session\mysqli\data\MySqlQuery;

class CombatStat{
	
	const TYPE_ALLTIME = 0;
	const TYPE_WEEKLY = 1;
	const TYPE_MONTHLY = 2;
	
	public bool $changed = false;
	
	public function __construct(
		public CombatComponent $combatComponent,
		
		public int $type,
		
		public int $kills = 0,
		public int $deaths = 0,
		
		public int $supplyDrops = 0,
		public int $moneyBags = 0,
	
		public int $mobs = 0
	){}
	
	public function getCombat() : CombatComponent{
		return $this->combatComponent;
	}
	
	public function getType() : int{
		return $this->type;
	}
	
	public function getKills() : int{
		return $this->kills;
	}
	
	public function setKills(int $kills) : void{
		$this->kills = $kills;
		$this->setChanged();
	}
	
	public function addKill(int $total = 1) : void{
		$this->setKills($this->getKills() + $total);
	}

	public function getDeaths() : int{
		return $this->deaths;
	}

	public function setDeaths(int $deaths) : void{
		$this->deaths = $deaths;
		$this->setChanged();
	}

	public function addDeath(int $total = 1) : void{
		$this->setDeaths($this->getDeaths() + $total);
	}

	public function getSupplyDrops() : int{
		return $this->supplyDrops;
	}

	public function setSupplyDrops(int $drops) : void{
		$this->supplyDrops = $drops;
		$this->setChanged();
	}

	public function addSupplyDrop(int $total = 1) : void{
		$this->setSupplyDrops($this->getSupplyDrops() + $total);
	}

	public function getMoneyBags() : int{
		return $this->moneyBags;
	}

	public function setMoneyBags(int $bags) : void{
		$this->moneyBags = $bags;
		$this->setChanged();
	}

	public function addMoneyBag(int $total = 1) : void{
		$this->setMoneyBags($this->getMoneyBags() + $total);
	}

	public function getMobs() : int{
		return $this->mobs;
	}

	public function setMobs(int $mobs) : void{
		$this->mobs = $mobs;
		$this->setChanged();
	}

	public function addMob(int $total = 1) : void{
		$this->setMobs($this->getMobs() + $total);
	}
	
	public function hasChanged() : bool{
		return $this->changed;
	}
	
	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
	}

	public function getQuery(User $user) : MySqlQuery{
		return new MySqlQuery(
			"combat_stats_" . $user->getXuid() . "_" . $this->getType(),
			"INSERT INTO combat_stats(
				xuid, ttype,
				kills, deaths,
				supply_drops, money_bags, mobs
			) VALUES(?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				kills=VALUES(kills),
				deaths=VALUES(deaths),
				supply_drops=VALUES(supply_drops),
				money_bags=VALUES(money_bags),
				mobs=VALUES(mobs)",
			[
				$user->getXuid(),
				$this->getType(),
				$this->getKills(), $this->getDeaths(),
				$this->getSupplyDrops(), $this->getMoneyBags(), $this->getMobs(),
			]
		);
	}

	/**
	 * Main-thread saving :3
	 */
	public function mtSave(User $user, \mysqli $db) : void{
		$xuid = $user->getXuid();
		
		$type = $this->getType();

		$kills = $this->getKills();
		$deaths = $this->getDeaths();
		$drops = $this->getSupplyDrops();
		$bags = $this->getMoneyBags();
		$mobs = $this->getMobs();

		$stmt = $db->prepare(
			"INSERT INTO combat_stats(
				xuid, ttype,
				kills, deaths,
				supply_drops, money_bags, mobs
			) VALUES(?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				kills=VALUES(kills),
				deaths=VALUES(deaths),
				supply_drops=VALUES(supply_drops),
				money_bags=VALUES(money_bags),
				mobs=VALUES(mobs)"
		);
		$stmt->bind_param("iiiiiii", $xuid, $type, $kills, $deaths, $drops, $bags, $mobs);
		$stmt->execute();
		$stmt->close();
	}
	
}
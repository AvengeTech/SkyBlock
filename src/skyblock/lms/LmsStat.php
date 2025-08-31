<?php namespace skyblock\lms;

use core\user\User;
use core\session\mysqli\data\MySqlQuery;

class LmsStat{

	const TYPE_ALLTIME = 0;
	const TYPE_WEEKLY = 1;
	const TYPE_MONTHLY = 2;

	public bool $changed = false;

	public function __construct(
		public LmsComponent $lmsComponent,

		public int $type,

		public int $kills = 0,
		public int $deaths = 0,

		public int $wins = 0,
		
		public int $cooldown = 0
	){}

	public function getLms() : LmsComponent{
		return $this->lmsComponent;
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

	public function getWins() : int{
		return $this->wins;
	}

	public function setWins(int $wins) : void{
		$this->wins = $wins;
		$this->setChanged();
	}

	public function addWin(int $total = 1) : void{
		$this->setWins($this->getWins() + $total);
	}

	public function getCooldown() : int{
		return $this->cooldown;
	}

	public function setCooldown(int $cooldown) : void{
		$this->cooldown = $cooldown;
		$this->setChanged();
	}

	public function hasChanged() : bool{
		return $this->changed;
	}

	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
	}

	public function getQuery(User $user) : MySqlQuery{
		return new MySqlQuery(
			"lms_stats_" . $user->getXuid() . "_" . $this->getType(),
			"INSERT INTO lms_stats(
				xuid, ttype,
				kills, deaths, wins,
				cooldown
			) VALUES(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				kills=VALUES(kills),
				deaths=VALUES(deaths),
				wins=VALUES(wins),
				cooldown=VALUES(cooldown)",
			[
				$user->getXuid(),
				$this->getType(),
				$this->getKills(), $this->getDeaths(),
				$this->getWins(),
				$this->getCooldown()
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
		$wins = $this->getWins();
		$cooldown = $this->getCooldown();

		$stmt = $db->prepare(
			"INSERT INTO lms_stats(
				xuid, ttype,
				kills, deaths, wins,
				cooldown
			) VALUES(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				kills=VALUES(kills),
				deaths=VALUES(deaths),
				wins=VALUES(wins),
				cooldown=VALUES(cooldown)"
		);
		$stmt->bind_param("iiiiii", $xuid, $type, $kills, $deaths, $wins, $cooldown);
		$stmt->execute();
		$stmt->close();
	}

}
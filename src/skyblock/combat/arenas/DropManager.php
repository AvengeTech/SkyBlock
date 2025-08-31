<?php namespace skyblock\combat\arenas;

use pocketmine\block\{
	Liquid,
	Leaves,
};
use pocketmine\entity\Location;
use pocketmine\player\Player;

use core\Core;
use skyblock\combat\arenas\entity\{
	SupplyDrop,
	MoneyBag
};

use core\utils\TextFormat;
use pocketmine\world\Position;
use skyblock\spawners\entity\hostile\CaveSpider;
use skyblock\spawners\entity\hostile\Husk;
use skyblock\spawners\entity\hostile\Spider;
use skyblock\spawners\entity\hostile\WitherSkeleton;
use skyblock\spawners\entity\hostile\Zombie;

class DropManager{

	const RATE_SUPPLY = 15 * 60;
	const RATE_MONEY = 5 * 60;
	const RATE_MOB_SPAWN = 3;

	const MOBS_PER_PLAYER = 9;
	const MOB_CLASSES = [
		Zombie::class,
		Husk::class,
		Spider::class,
		CaveSpider::class,
		WitherSkeleton::class
	];
	
	public int $ticks = 0;
	
	public array $spawnedSupplyDrops = [];
	public array $spawnedMoneyBags = [];

	public array $spawnedMobs = [];
	
	public function __construct(
		public Arena $arena,
		public array $supplyDropPositions = [],
		public array $moneyBagPositions = []
	){}
	
	public function tick() : void{
		$this->ticks++;
		if($this->ticks % self::RATE_SUPPLY === 0){
			Core::announceToSS(TextFormat::AQUA . TextFormat::BOLD . ">>> " . TextFormat::RESET . TextFormat::YELLOW . "Supply drop has spawned in the warzone!");
			$this->spawnSupplyDrop();
		}
		if($this->ticks % self::RATE_MONEY === 0){
			$this->spawnMoneyBag();
		}
		foreach($this->getSpawnedSupplyDrops() as $id => $entity){
			if(!$entity->isAlive() || $entity->isClosed())
				unset($this->spawnedSupplyDrops[$id]);
		}

		foreach($this->getSpawnedMoneyBags() as $id => $entity){
			if(!$entity->isAlive() || $entity->isClosed())
				unset($this->spawnedMoneyBags[$id]);
		}

		foreach($this->getSpawnedMobs() as $id => $entity){
			if(!$entity->isAlive() || $entity->isClosed())
				unset($this->spawnedMobs[$id]);
		}
		if(
			$this->ticks % self::RATE_MOB_SPAWN === 0 &&

			($spwned = count($this->getSpawnedMobs())) <
			($cnt = count($this->getArena()->getPlayers()) * self::MOBS_PER_PLAYER) &&

			mt_rand(1, 3) === 3
		){
			$players = $this->getArena()->getPlayers(true);
			if(count($players) === 0) return;
			
			$player = $players[array_rand($players)];
			//$this->spawnMob($player);
		}
	}
	
	public function getArena() : Arena{
		return $this->arena;
	}
	
	public function getSupplyDropPositions() : array{
		return $this->supplyDropPositions;
	}
	
	public function getRandomSupplyDropLocation() : Location{
		$spawn = $this->supplyDropPositions[mt_rand(0, count($this->supplyDropPositions) - 1)];
		return Location::fromObject($spawn->add(0, 50, 0), $this->getArena()->getWorld(), mt_rand(0, 359));
	}

	public function spawnSupplyDrop() : void{
		$location = $this->getRandomSupplyDropLocation();

		$drop = new SupplyDrop($location);
		$drop->spawnToAll();

		$this->spawnedSupplyDrops[$drop->getId()] = $drop;
	}
	
	public function getSpawnedSupplyDrops() : array{
		return $this->spawnedSupplyDrops;
	}

	public function getMoneyBagPositions() : array{
		return $this->moneyBagPositions;
	}

	public function getRandomMoneyBagLocation() : Location{
		$spawn = $this->moneyBagPositions[mt_rand(0, count($this->moneyBagPositions) - 1)];
		return Location::fromObject($spawn, $this->getArena()->getWorld(), mt_rand(0, 359));
	}

	public function spawnMoneyBag(int $worth = -1, float $scale = 1.0) : void{
		$bag = new MoneyBag($this->getRandomMoneyBagLocation(), null, $worth, -1, $scale);
		$bag->spawnToAll();
		$this->spawnedMoneyBags[$bag->getId()] = $bag;
	}

	public function getSpawnedMoneyBags() : array{
		return $this->spawnedMoneyBags;
	}

	public function getNearbySpawnLocation(Position $pos, int $range = 20, int $tries = 16) : Location{
		while($tries > 0){
			$tries--;
			$x = mt_rand(-$range, $range) + (int) $pos->x;
			$z = mt_rand(-$range, $range) + (int) $pos->z;

			$y = $pos->getWorld()->getHighestBlockAt($x, $z);
			if($y === null) continue;
			if(
				($block = $pos->getWorld()->getBlockAt($x, $y, $z)) instanceof Liquid ||
				$block instanceof Leaves
			) continue;
			break;
		}

		return new Location($x, $y + 1, $z, $pos->getWorld(), mt_rand(0, 360), 0);
	}

	public function getSpawnedMobs() : array{
		return $this->spawnedMobs;
	}

	public function spawnMob(Player|Position $where, int $range = 25, string $class = "") : void{
		if($where instanceof Player){
			if(!$this->getArena()->inArena($where)) return;

			$where = $where->getPosition();
		}

		$pos = $this->getNearbySpawnLocation($where, $range);
		$class = $class === "" ? self::MOB_CLASSES[mt_rand(0, count(self::MOB_CLASSES) - 1)] : $class;

		$entity = new $class($pos, null, true, false);
		//todo: chance of wielding sht

		$entity->spawnToAll();
		$this->spawnedMobs[] = $entity;
	}
	
}
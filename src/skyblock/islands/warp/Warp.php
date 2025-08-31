<?php namespace skyblock\islands\warp;

use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

use skyblock\SkyBlock;

use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\staff\anticheat\session\SessionManager;
use core\utils\TextFormat;

class Warp{

	const LIMIT_NAME = 16;
	const LIMIT_DESCRIPTION = 32;

	public bool $changed = false;

	public Location $location;

	public function __construct(
		public WarpManager $warpManager,

		public int $created,

		public string $name,
		public string $description,
		public int $hierarchy,

		Vector3 $vector3,
		int $yaw = -1
	){
		$this->location = Location::fromObject($vector3, $warpManager->getIsland()->getWorld(), $yaw);
	}

	public function getWarpManager() : WarpManager{
		return $this->warpManager;
	}

	public function getCreated() : int{
		return $this->created;
	}

	public function getName() : string{
		return $this->name;
	}

	public function setName(string $name) : void{
		$this->name = $name;
		$this->setChanged();
	}

	public function getDescription() : string{
		return $this->description;
	}

	public function setDescription(string $description = "") : void{
		$this->description = $description;
		$this->setChanged();
	}

	public function getHierarchy() : int{
		return $this->hierarchy;
	}

	public function setHierarchy(int $hierarchy = 0) : void{
		$this->hierarchy = $hierarchy;
		$this->setChanged();
	}

	public function getLocation() : Location{
		return $this->location;
	}

	public function updateLocation(float $x, float $y, float $z, int $yaw) : void{
		$this->location = Location::fromObject(new Vector3($x, $y, $z), $this->getWarpManager()->getIsland()->getWorld(), $yaw);
		$this->setChanged();
	}

	public function hasChanged() : bool{
		return $this->changed;
	}

	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
	}

	public function delete() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest("delete_island_warp_" . $this->getWarpManager()->getIsland()->getWorldName() . "_" . $this->getName(), new MySqlQuery("main",
				"DELETE FROM island_warps WHERE world=? AND created=?",
				[
					$this->getWarpManager()->getIsland()->getWorldName(),
					$this->getCreated(),
				]
			)),
			function(MySqlRequest $request) : void{}
		);
	}

	public function save(bool $async = true) : void{
		if(!$this->hasChanged()) return;
		if($async){
			SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
				new MySqlRequest("save_island_warp_" . $this->getWarpManager()->getIsland()->getWorldName() . "_" . $this->getName(), new MySqlQuery("main",
					"INSERT INTO island_warps(world, created, name, description, hierarchy, locx, locy, locz, yaw) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
						name=VALUES(name), description=VALUES(description), hierarchy=VALUES(hierarchy),
						locx=VALUES(locx), locy=VALUES(locy), locz=VALUES(locz), yaw=VALUES(yaw)",
					[
						$this->getWarpManager()->getIsland()->getWorldName(),
						$this->getCreated(),
						$this->getName(), $this->getDescription(), $this->getHierarchy(),
						$this->getLocation()->getX(), $this->getLocation()->getY(), $this->getLocation()->getZ(),
						$this->getLocation()->getYaw()
					]
				)),
				function(MySqlRequest $request) : void{
					$this->setChanged(false);	
				}
			);
		}else{
			$worldName = $this->getWarpManager()->getIsland()->getWorldName();
			$created = $this->getCreated();
			$name = $this->getName();
			$description = $this->getDescription();
			$hierarchy = $this->getHierarchy();
			$x = $this->getLocation()->getX();
			$y = $this->getLocation()->getY();
			$z = $this->getLocation()->getZ();
			$yaw = $this->getLocation()->getYaw();

			$db = SkyBlock::getInstance()->getSessionManager()->getDatabase();
			$stmt = $db->prepare("INSERT INTO island_warps(world, created, name, description, hierarchy, locx, locy, locz, yaw) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				name=VALUES(name), description=VALUES(description), hierarchy=VALUES(hierarchy),
				locx=VALUES(locx), locy=VALUES(locy), locz=VALUES(locz), yaw=VALUES(yaw)"
			);
			$stmt->bind_param("sissidddi", $worldName, $created, $name, $description, $hierarchy, $x, $y, $z, $yaw);
			$stmt->execute();
			$stmt->close();

			$this->setChanged(false);
		}
	}

	/**
	 * Player must be at island to use this
	 */
	public function teleportTo(Player $player) : void{
		$player->teleport($this->getLocation()->getYaw() == -1 ? $this->getLocation()->asPosition() : $this->getLocation());
		$player->sendTitle(TextFormat::AQUA . $this->getName(), $this->getDescription() !== "" ? TextFormat::GRAY . $this->getDescription() : "", 10, 30, 10);
	}

}
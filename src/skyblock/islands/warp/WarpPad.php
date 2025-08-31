<?php namespace skyblock\islands\warp;

use pocketmine\math\Vector3;

use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};

use skyblock\SkyBlock;

class WarpPad{
	
	public function __construct(
		public WarpManager $warpManager,
		public string $warpName,
		public Vector3 $position
	){}
	
	public function getWarpManager() : WarpManager{
		return $this->warpManager;
	}
	
	public function getWarpName() : string{
		return $this->warpName;
	}
	
	public function getWarp() : ?Warp{
		return $this->getWarpManager()->getWarp($this->getWarpName());
	}
	
	public function getPosition() : Vector3{
		return $this->position;
	}

	public function getKey() : string{
		return $this->getPosition()->getX() . ":" . $this->getPosition()->getY() . ":" . $this->getPosition()->getZ();
	}

	public function delete() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest("delete_island_warp_pad_" . $this->getWarpManager()->getIsland()->getWorldName() . "_" . $this->getKey(), new MySqlQuery("main",
				"DELETE FROM island_warp_pads WHERE world=? AND posx=? AND posy=? AND posz=?",
				[
					$this->getWarpManager()->getIsland()->getWorldName(),
					$this->getWarp()->getCreated(),
				]
			)),
			function(MySqlRequest $request) : void{}
		);
	}

	public function save(bool $async = true) : void{
		if($async){
			SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
				new MySqlRequest("save_island_warp_pad_" . $this->getWarpManager()->getIsland()->getWorldName() . "_" . $this->getKey(), new MySqlQuery("main",
					"INSERT INTO island_warp_pads(world, warp, posx, posy, posz) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
						warp=VALUES(warp),
						posx=VALUES(posx), posy=VALUES(posy), posz=VALUES(posz)",
					[
						$this->getWarpManager()->getIsland()->getWorldName(),
						$this->getWarpName(),
						$this->getPosition()->getX(), $this->getPosition()->getY(), $this->getPosition()->getZ(),
					]
				)),
				function(MySqlRequest $request) : void{
					//$this->setChanged(false);
				}
			);
		}else{
			$worldName = $this->getWarpManager()->getIsland()->getWorldName();
			$warp = $this->getWarpName();
			$x = $this->getPosition()->getX();
			$y = $this->getPosition()->getY();
			$z = $this->getPosition()->getZ();

			$db = SkyBlock::getInstance()->getSessionManager()->getDatabase();
			$stmt = $db->prepare("INSERT INTO island_warp_pads(world, warp, posx, posy, posz) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
						warp=VALUES(warp),
						posx=VALUES(posx), posy=VALUES(posy), posz=VALUES(posz)"
			);
			$stmt->bind_param("ssiii", $worldName, $warp, $x, $y, $z);
			$stmt->execute();
			$stmt->close();

			//$this->setChanged(false);
		}
	}
}
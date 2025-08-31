<?php namespace skyblock\islands;

use pocketmine\player\{
	Player
};

use skyblock\SkyBlock;
use skyblock\islands\permission\{
	PlayerPermissions,
	Permissions
};
use skyblock\islands\warp\Warp;

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use skyblock\SkyBlockPlayer;

class IslandsComponent extends SaveableComponent{

	/** @var PlayerPermissions[] */
	public array $permissions = [];
	public ?string $islandAt = "";
	public ?Warp $warpMode = null;

	public bool $signShopMode = false;

	public ?Island $lastIslandAt = null;

	public function getName() : string{
		return "islands";
	}

	public function addPermission(PlayerPermissions $permissions) : void{
		$this->permissions[$permissions->getIslandWorld()] = $permissions;
	}

	public function removePermission(PlayerPermissions $permissions) : void{
		unset($this->permissions[$permissions->getIslandWorld()]);
	}

	/** @return PlayerPermissions[] */
	public function getPermissions() : array{
		return $this->permissions;
	}

	public function getOwnerPermissions() : array{
		$permissions = [];
		foreach($this->getPermissions() as $world => $permission){
			if($permission->getPermission(Permissions::OWNER)){
				$permissions[$world] = $permission;
			}
		}
		return $permissions;
	}

	public function getTotalAllowedIslands(Player $player) : int{
		/** @var SkyBlockPlayer $player */
		return match($player->getRank()){
			"owner" => 10,
			"developer" => 10,
			"enderdragon" => 2,
			"trainee" => 2,
			"mod" => 2,
			default => 1
		};
	}

	public function getPermissionsFor(string $world) : ?PlayerPermissions{
		return $this->permissions[$world] ?? null;
	}

	public function atIsland() : bool{
		return $this->getIslandAtId() !== "";
	}

	public function atValidIsland() : bool{
		return $this->getIslandAt() !== null;
	}

	public function getIslandAtId() : string{
		return $this->islandAt;
	}

	public function getIslandAt() : ?Island{
		return SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->getIslandAtId());
	}

	public function setIslandAt(?Island $island = null) : void{
		if(($is = $this->getIslandAt()) !== null){
			if(count($is->getPlayers()) <= 1){
				$is->save();
			}
		}
		if($island === null && ($pl = $this->getPlayer()) !== null){
			$ia = $this->getIslandAt();
			if($ia !== null){
				$ia->removeScoreboard($pl);
				$this->setLastIslandAt($ia);
			}
			$this->islandAt = "";
			$pl->setFlightMode(false);
			return;
		}
		$this->islandAt = $island !== null ? $island->getWorldName() : "";
	}

	public function inWarpMode() : bool{
		return $this->warpMode !== null;
	}

	public function getWarpMode() : Warp{
		return $this->warpMode;
	}

	public function setWarpMode(?Warp $warp = null) : void{
		$this->warpMode = $warp;
	}

	public function inShopMode() : bool{
		return $this->signShopMode;
	}

	public function setShopMode(bool $mode = true) : void{
		$this->signShopMode = $mode;
	}

	public function getLastIslandAt() : ?Island{
		return $this->lastIslandAt;
	}

	public function setLastIslandAt(?Island $island = null) : void{
		$this->lastIslandAt = $island;
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([
			//"DROP TABLE islands", "DROP TABLE island_permissions",
			"CREATE TABLE IF NOT EXISTS islands(
				world VARCHAR(64) NOT NULL PRIMARY KEY,
				islandType INT NOT NULL,
    				created INT NOT NULL,
				iname VARCHAR(32) NOT NULL,
				description VARCHAR(256) NOT NULL,
				sizelevel INT NOT NULL,
				time INT NOT NULL DEFAULT -1,
				public TINYINT(1) NOT NULL,
				permissionVersion VARCHAR(10) NOT NULL,
				defaultVisitorPermissions VARCHAR(8000) NOT NULL,
				defaultInvitePermissions VARCHAR(8000) NOT NULL,
				blockList BLOB NOT NULL,
				spawnx DECIMAL(7, 1) NOT NULL,
				spawny DECIMAL(7, 1) NOT NULL,
				spawnz DECIMAL(7, 1) NOT NULL,
				gens INT NOT NULL,
				spawners INT NOT NULL,
				hoppers INT NOT NULL
			)",
			"CREATE TABLE IF NOT EXISTS island_permissions(
				xuid BIGINT(16) NOT NULL,
				world VARCHAR(32) NOT NULL,
    				created INT NOT NULL,
				version VARCHAR(10) NOT NULL,
				permissions VARCHAR(10000) NOT NULL DEFAULT '{}',
				PRIMARY KEY(xuid, world)
			)",
			"CREATE TABLE IF NOT EXISTS island_warps(
				world VARCHAR(32) NOT NULL,
				created INT NOT NULL,
				name VARCHAR(16) NOT NULL,
				description VARCHAR(32) NOT NULL DEFAULT ' ',
				hierarchy INT(5) NOT NULL,
				locx DECIMAL(7, 1) NOT NULL,
				locy DECIMAL(7, 1) NOT NULL,
				locz DECIMAL(7, 1) NOT NULL,
				yaw INT(3) NOT NULL DEFAULT -1,
				PRIMARY KEY(world, created)
			)",
			"CREATE TABLE IF NOT EXISTS island_warp_pads(
				world VARCHAR(32) NOT NULL,
				warp VARCHAR(16) NOT NULL,
				posx INT NOT NULL,
				posy INT NOT NULL,
				posz INT NOT NULL,
				PRIMARY KEY(world, posx, posy, posz)
			)",
			//"DROP TABLE IF EXISTS island_shops",
			"CREATE TABLE IF NOT EXISTS island_shops(
				world VARCHAR(32) NOT NULL,
				created INT NOT NULL,
				name VARCHAR(16) NOT NULL,
				description VARCHAR(256) NOT NULL DEFAULT ' ',
				hierarchy INT(5) NOT NULL,
				posx INT NOT NULL,
				posy INT NOT NULL,
				posz INT NOT NULL,
				bank INT NOT NULL DEFAULT 0,
				shopitems BLOB NOT NULL,
				PRIMARY KEY(world, created)
			)",
			//"DROP TABLE IF EXISTS island_texts",
			"CREATE TABLE IF NOT EXISTS island_texts(
				world VARCHAR(32) NOT NULL,
				created INT NOT NULL,
				textdata VARCHAR(777) NOT NULL,
				posx DECIMAL(6,1) NOT NULL,
				posy DECIMAL(6,1) NOT NULL,
				posz DECIMAL(6,1) NOT NULL,
				PRIMARY KEY(world, created)
			)",
			//"DROP TABLE IF EXISTS island_challenges",
			"CREATE TABLE IF NOT EXISTS island_challenges(
				world VARCHAR(32) NOT NULL,
				challenges BLOB NOT NULL,
				PRIMARY KEY(world)
			)"
		] as $query) $db->query($query);
	}

	public function loadAsync() : void{
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM island_permissions WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		$result = $request->getQuery()->getResult();
		$rows = $result->getRows();
		foreach($rows as $row){
			$permissions = new PlayerPermissions($this->getUser(), $row["world"], $row["created"], $row["version"], json_decode($row["permissions"], true));
			$this->addPermission($permissions);
		}
		foreach($this->getPermissions() as $permission){
			$permission->checkUpdate();
		}
		parent::finishLoadAsync($request);
	}

	public function saveAsync() : void{
	}

	public function getSerializedData(): array {
		$perms = [];
		foreach ($this->getPermissions() as $perm) {
			$perms[] = [
				"world" => $perm->getIslandWorld(),
				"created" => $perm->getCreated(),
				"version" => $perm->getVersion()->toString(),
				"permissions" => json_encode($perm->getPermissions())
			];
		}
		return [
			"permissions" => $perms
		];
	}

	public function applySerializedData(array $data): void {
		foreach ($data["permissions"] as $perm) {
			$permissions = new PlayerPermissions($this->getUser(), $perm["world"], $perm["created"], $perm["version"], json_decode($perm["permissions"], true));
			$this->addPermission($permissions);
		}
		foreach ($this->getPermissions() as $permission) {
			$permission->checkUpdate();
		}
	}

}

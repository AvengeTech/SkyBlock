<?php namespace skyblock\islands\permission;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\user\User;
use core\utils\Version;
use pocketmine\player\GameMode;

class PlayerPermissions{

	public Version $version;

	public bool $saving = false;
	public bool $changed = false;

	public function __construct(
		public User $user,
		public string $world,
		public int $created,
		string $version,
		public array $permissions = []
	){
		$this->setVersion(Version::fromString($version));
	}

	public function checkUpdate() : bool{
		if(!$this->getVersion()->equals(Permissions::VERSION)){
			foreach(Permissions::INVITE_PERMISSION_UPDATES as $version => $permissions){
				if(!$this->getVersion()->newerThan($version)){
					foreach($permissions as $key => $value){
						$this->permissions[$key] = $value;
					}
				}
			}
			$this->version = Version::fromString(Permissions::VERSION);
			$this->setChanged();
			$this->save();
			return true;
		}
		return false;
	}

	public function getUser() : User{
		return $this->user;
	}

	public function getIslandWorld() : string{
		return $this->world;
	}

	public function getCreated() : int{
		return $this->created;
	}

	public function getVersion() : Version{
		return $this->version;
	}

	public function setVersion(Version $version) : void{
		$this->version = $version;
	}

	public function getPermissions() : array{
		return $this->permissions;
	}

	public function isOwner() : bool{
		return $this->getPermission(Permissions::OWNER);
	}

	public function getHierarchy() : int{
		return $this->getPermission(Permissions::HIERARCHY);
	}

	public function setHierarchy(int $value) : void{
		$this->setPermission(Permissions::HIERARCHY, $value);
	}

	public function getPermission(int $key) : mixed{
		return $this->permissions[$key] ?? false;
	}

	public function setPermission(int $key, mixed $value) : void{
		$this->permissions[$key] = $value;
		$this->setChanged();
	}

	public function hasChanged() : bool{
		return $this->changed;
	}

	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
		$this->recalculateFlyMode();
	}

	public function recalculateFlyMode(): void {
		$player = $this->getUser()->getPlayer();
		if ($player instanceof SkyBlockPlayer) {
			if ($player->getGamemode() == GameMode::ADVENTURE() && ($this->getPermission(Permissions::EDIT_BLOCKS) || $this->getPermission(Permissions::EDIT_ORE_FROM_ORE_GENS))) {
				$player->setGamemode(GameMode::SURVIVAL());
			} else {
				$player->setGamemode(GameMode::ADVENTURE());
			}
		}
	}

	public function delete() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest("delete_permissions_" . $this->getIslandWorld() . "_" . $this->getUser()->getXuid(), new MySqlQuery("main",
				"DELETE FROM island_permissions WHERE xuid=? AND world=?",
				[$this->getUser()->getXuid(), $this->getIslandWorld()]
			)),
			function(MySqlRequest $request){}
		);
		if(($pl = $this->getUser()->getPlayer()) instanceof Player && $pl->isLoaded()){
			/** @var SkyBlockPlayer $pl */
			$pl->getGameSession()->getIslands()?->removePermission($this);
		}
	}

	public function isSaving() : bool{
		return $this->saving;
	}

	public function save(bool $async = true) : void{
		if(!$this->hasChanged()) return;

		if($async){
			$this->saving = true;
			SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
				new MySqlRequest("save_island_permission_" . $this->getIslandWorld() . "_" . $this->getUser()->getXuid(), new MySqlQuery("main",
					"INSERT INTO island_permissions(xuid, world, created, version, permissions) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE version=VALUES(version), permissions=VALUES(permissions)",
					[
						$this->getUser()->getXuid(), $this->getIslandWorld(),
						$this->getCreated(),
						$this->getVersion()->toString(), json_encode($this->getPermissions())
					]
				)),
				function(MySqlRequest $request){
					$this->saving = false;
				}
			);
		}else{
			$xuid = $this->getUser()->getXuid();
			$world = $this->getIslandWorld();
			$created = $this->getCreated();
			$version = $this->getVersion()->toString();
			$permissions = json_encode($this->getPermissions());

			$db = SkyBlock::getInstance()->getSessionManager()->getDatabase();
			$stmt = $db->prepare("INSERT INTO island_permissions(xuid, world, created, version, permissions) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE version=VALUES(version), permissions=VALUES(permissions)");
			$stmt->bind_param("isiss", $xuid, $world, $created, $version, $permissions);
			$stmt->execute();
			$stmt->close();

			$this->setChanged(false);
		}
	}

}
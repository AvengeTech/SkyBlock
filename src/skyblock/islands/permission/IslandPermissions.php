<?php namespace skyblock\islands\permission;

use core\rank\Rank;
use skyblock\islands\Island;
use skyblock\SkyBlockPlayer as Player;

use core\user\User;
use core\utils\Version;

class IslandPermissions{

	public Version $version;

	public PlayerPermissions $defaultVisitorPermissions;
	public PlayerPermissions $defaultInvitePermissions;
	public PlayerPermissions $unlockedPermissions;

	public function __construct(
		public Island $island,

		string $permissionVersion,
		array $defaultVisitorPermissions,
		array $defaultInvitePermissions,

		public array $permissions = []
	){
		$permissionVersion = $this->version = Version::fromString(Permissions::VERSION);
		if(!$permissionVersion->equals(Permissions::VERSION)){
			foreach(Permissions::VISITOR_PERMISSION_UPDATES as $version => $permissions){
				if(!$permissionVersion->newerThan($version)){
					foreach($permissions as $key => $value){
						$defaultVisitorPermissions[$key] = $value;
					}
				}
			}

			foreach(Permissions::INVITE_PERMISSION_UPDATES as $version => $permissions){
				if(!$permissionVersion->newerThan($version)){
					foreach($permissions as $key => $value){
						$defaultInvitePermissions[$key] = $value;
					}
				}
			}
			$permissionVersion = Permissions::VERSION;
			$this->version = Version::fromString(Permissions::VERSION);
		}

		$this->defaultInvitePermissions = new PlayerPermissions(new User(0, "Default Invite"), $island->getWorldName(), time(), $permissionVersion, $defaultInvitePermissions);
		$this->defaultVisitorPermissions = new PlayerPermissions(new User(0, "Default Visitor"), $island->getWorldName(), time(), $permissionVersion, $defaultVisitorPermissions);
		$this->unlockedPermissions = new PlayerPermissions(new User(0, "Unlocked Permissions"), $island->getWorldName(), time(), $permissionVersion, Permissions::UNLOCKED_PERMISSIONS);

		foreach($this->getPermissions() as $permission){
			$permission->checkUpdate();
		}
	}

	public function getIsland() : Island{
		return $this->island;
	}

	public function getVersion() : Version{
		return $this->version;
	}

	public function getDefaultVisitorPermissions() : PlayerPermissions{
		return $this->defaultVisitorPermissions;
	}

	public function updateDefaultVisitorPermissions(PlayerPermissions $permissions) : void{
		$this->defaultVisitorPermissions = $permissions;
	}

	public function getDefaultInvitePermissions() : PlayerPermissions{
		return $this->defaultInvitePermissions;
	}

	public function updateDefaultInvitePermissions(PlayerPermissions $permissions) : void{
		$this->defaultInvitePermissions = $permissions;
	}

	public function getPermissions() : array{
		return $this->permissions;
	}
	
	public function getMembersOnIsland() : array{
		$online = [];
		foreach($this->getPermissions() as $permission){
			if(
				($player = $permission->getUser()->getPlayer()) instanceof Player &&
				$player->isLoaded() &&
				($ses = $player->getGameSession()->getIslands())->atIsland() &&
				$ses->getIslandAt()?->getWorldName() == $this->getIsland()?->getWorldName()
			) $online[] = $permission;
		}
		return $online;
	}

	public function isMember(User|Player $player) : bool{
		return $this->getPermissionsBy($player) !== null;
	}

	public function getTotalMembersAllowed() : int{
		return match($this->getOwner()->getUser()->getRankHierarchy()){
			1 => 3,
			2 => 4,
			3 => 5,
			4 => 6,
			5 => 7,
			6 => 10,
			8 => 12,
			9 => 12,
			10 => 12,
			50 => 12,
			69 => 100,
			default => 2
		};
	}
	
	public function getOwner() : ?PlayerPermissions{
		foreach($this->getPermissions() as $permission){
			if($permission->getPermission(Permissions::OWNER)){
				return $permission;
			}
		}
		return new PlayerPermissions(new User(0, "no owner"), $this->getIsland()->getWorldName(), time(), "1.0.0"); //shouldn't happen
	}

	public function getPermissionsBy(User|Player $user) : ?PlayerPermissions{
		if ($user->getRankHierarchy() >= Rank::HIERARCHY_SR_MOD) return $this->unlockedPermissions;
		return $this->permissions[$user->getXuid()] ?? null;
	}

	public function updatePermissions(PlayerPermissions $permissions){
		$this->addPermissions($permissions);

		$player = $permissions->getUser()->getPlayer();
		if($player instanceof Player){
			if($player->inFlightMode() && !$permissions->getPermission(Permissions::USE_FLY)){
				$player->setFlightMode(false);
			}
		}
	}

	public function addPermissions(PlayerPermissions $permissions, bool $save = false) : void{
		$this->permissions[$permissions->getUser()->getXuid()] = $permissions;
		if($save) $permissions->save();
	}

	public function addNewDefaultPermissions(User $user, bool $save = false) : PlayerPermissions{
		$permissions = new PlayerPermissions(
			$user,
			$this->getIsland()->getWorldName(),
			time(),
			Permissions::VERSION,
			$this->getDefaultInvitePermissions()->getPermissions()
		);
		$permissions->setChanged();
		$this->addPermissions($permissions, $save);
		return $permissions;
	}

	public function removePermissions(PlayerPermissions $permissions, bool $delete = true) : void{
		unset($this->permissions[$permissions->getUser()->getXuid()]);
		if($delete) $permissions->delete();

		if(($player = $permissions->getUser()->getPlayer()) instanceof Player){
			if($player->isLoaded()){
				$ses = $player->getGameSession()->getIslands();
				$ses->removePermission($permissions);
				if(
					$ses->atIsland() &&
					$ses->getIslandAt()->getWorldName() == $this->getIsland()->getWorldName()
				){
					$this->getIsland()->kick($player, "Your permissions to this island were revoked");
				}
			}
		}
	}
	
	public function delete() : void{
		foreach($this->getPermissions() as $permission){
			$permission->delete();
		}
	}
	
	public function save(bool $async = true) : void{
		foreach($this->getPermissions() as $permission){
			$permission->save($async);
		}
	}

}
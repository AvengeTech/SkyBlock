<?php
namespace skyblock\islands\command;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\Core;
use core\user\User;
use core\utils\TextFormat;
use pocketmine\Server;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer as Player;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\entity\IslandEntity;
use skyblock\islands\permission\Permissions;
use skyblock\islands\ui\IslandsUi;
use skyblock\islands\ui\access\MyIslandsUi;
use skyblock\islands\ui\access\staff\VisitAnyIslandUi;
use skyblock\islands\ui\help\CommandHelpUi;
use skyblock\islands\ui\manage\IslandInfoUi;
use skyblock\islands\ui\manage\invite\InvitePlayerUi;
use skyblock\islands\ui\manage\invite\MyInvitesUi;
use skyblock\islands\text\ui\IslandTextsUi;
use skyblock\islands\warp\ui\IslandWarpsUi;
use skyblock\settings\SkyBlockSettings;
use skyblock\islands\challenge\ChallengeManager;
use skyblock\islands\invite\Invite;
use skyblock\islands\permission\PlayerPermissions;

class IslandCommand extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["is", "i"]);
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			$sender->showModal(new IslandsUi($sender));
			return;
		}
		$session = $sender->getGameSession()->getIslands();
		$atIsland = $session->atIsland();
		$island = $session->getIslandAt();

		$action = strtolower(array_shift($args));
		switch ($action) {
			case "go":
			case "home":
				if (count($session->getPermissions()) === 0) {
					$sender->sendMessage(TextFormat::RI . "You do not have an island!");
					return;
				}
				$worlds = [];
				foreach ($session->getPermissions() as $permission) {
					$worlds[] = $permission->getIslandWorld();
				}
				SkyBlock::getInstance()->getIslands()->getIslandManager()->loadIslands($worlds, function (array $islands) use ($worlds, $sender): void {
					if (!$sender->isConnected()) return;
					if (($count = count($islands)) == 0) {
						$sender->sendMessage(TextFormat::RI . "You do not have an island!");
					} elseif ($count === 1) {
						$island = array_shift($islands);
						SkyBlock::getInstance()->getIslands()->getIslandManager()->gotoIsland($sender, $island);
					} elseif (
						($def = $sender->getGameSession()->getSettings()->getSetting(SkyBlockSettings::DEFAULT_ISLAND)) !== null &&
						in_array($def, $worlds)
					) {
						foreach ($islands as $island) {
							if ($island->getWorldName() === $def) {
								SkyBlock::getInstance()->getIslands()->getIslandManager()->gotoIsland($sender, $island);
								return;
							}
						}
					} else {
						$sender->showModal(new MyIslandsUi($sender, $islands));
					}
				});
				break;

			case "chat":
			case "c":
				($ss = $sender->getGameSession()->getSettings())->setSetting(SkyBlockSettings::ISLAND_CHAT, $active = !$ss->getSetting(SkyBlockSettings::ISLAND_CHAT));
				$sender->sendMessage(TextFormat::GI . "Island chat has been " . ($active ? "enabled" : "disabled") . "!");
				break;

			case "menu":
			case "m":
				if (!$atIsland) {
					$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
					return;
				}
				$sender->showModal(new IslandInfoUi($sender, $island));
				break;

			case "spawn":
				if (!$atIsland) {
					$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
					return;
				}
				$island->teleportTo($sender);
				break;

			case "setspawn":
				if (!$atIsland) {
					$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
					return;
				}
				$perms = $island->getPermissions()->getPermissionsBy($sender);
				if ($perms === null || !$perms->getPermission(Permissions::EDIT_ISLAND)) {
					$sender->sendMessage(TextFormat::RI . "You do not have permission to edit this island!");
					return;
				}
				$island->setSpawnpoint($sender->getPosition()->asVector3());
				$sender->sendMessage(TextFormat::GI . "Spawnpoint was set to your current position!");
				break;

			case "warps":
			case "warp":
				if (!$atIsland) {
					$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
					return;
				}
				$perms = $island->getPermissions()->getPermissionsBy($sender) ?? $island->getPermissions()->getDefaultVisitorPermissions();
				if (!$perms->getPermission(Permissions::USE_WARPS)) {
					$sender->sendMessage(TextFormat::RI . "You do not have permission to use warps on this island!");
					return;
				}
				if (count($args) === 0) {
					$sender->showModal(new IslandWarpsUi($sender, $island));
					return;
				}
				break;

			case "movemenu":
			case "move":
				if (!$atIsland) {
					$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
					return;
				}
				$perms = $island->getPermissions()->getPermissionsBy($sender) ?? $island->getPermissions()->getDefaultVisitorPermissions();
				if (!$perms->getPermission(Permissions::MOVE_ISLAND_MENU)) {
					$sender->sendMessage(TextFormat::RI . "You do not have permission to move the island menu!");
					return;
				}
				if (($ie = $island->getIslandEntity()) === null || $ie->isClosed() || $ie->isFlaggedForDespawn()) {
					$ie = new IslandEntity($sender->getLocation());
					$ie->spawnToAll();
				} else {
					$ie->teleport($sender->getPosition());
				}
				$sender->sendMessage(TextFormat::RI . "Island menu has been moved!");
				break;

			case "inv":
			case "invite":
				if (!$atIsland) {
					$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
					return;
				}
				$perms = $island->getPermissions()->getPermissionsBy($sender) ?? $island->getPermissions()->getDefaultVisitorPermissions();
				if (!$perms->getPermission(Permissions::EDIT_MEMBERS)) {
					$sender->sendMessage(TextFormat::RI . "You do not have permission to invite island members!");
					return;
				}
				if (count($island->getPermissions()->getPermissions()) + SkyBlock::getInstance()->getIslands()->getInviteManager()->getInvitesTo($island) >= $island->getPermissions()->getTotalMembersAllowed()) {
					$sender->sendMessage(TextFormat::RI . "This island has the max amount of members allowed!");
					return;
				}
				if (count($args) > 0) {
					$username = strtolower(array_shift($args));
					Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($sender, $island): void {
						if (!$sender->isConnected()) return;
						if (!$user->valid()) {
							$sender->sendMessage(TextFormat::RI . "Player never seen!");
							return;
						}
						if ($island->isBlocked($user)) {
							$sender->sendMessage(TextFormat::RI . "That player is blocked from this island!");
							return;
						}
						if ($island->getPermissions()->getPermissionsBy($user) !== null) {
							$sender->sendMessage(TextFormat::RI . "Player is already a member of this island!");
							return;
						}

						$im = SkyBlock::getInstance()->getIslands()->getInviteManager();
						if ($im->hasInviteTo($user, $island)) {
							$sender->sendMessage(TextFormat::RI . "Player already has an outgoing invite to this island!");
							return;
						}

						$im->sendInvite(new Invite(
							$island,
							$sender->getUser(),
							$user
						));

						$sender->sendMessage(TextFormat::GI . TextFormat::YELLOW . $user->getGamertag() . TextFormat::GREEN . " has been invited!");
					});
					return;
				}
				$sender->showModal(new InvitePlayerUi($island));
				break;

			case "invites":
				if (count(SkyBlock::getInstance()->getIslands()->getInviteManager()->getInvitesFor($sender)) === 0) {
					$sender->sendMessage(TextFormat::RI . "You have no incoming island invites!");
					return;
				}
				$sender->showModal(new MyInvitesUi($sender));
				break;

			case "signshop":
			case "ss":
				if (!$atIsland) {
					$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
					return;
				}
				$perms = $island->getPermissions()->getPermissionsBy($sender) ?? $island->getPermissions()->getDefaultVisitorPermissions();
				if (!$perms->getPermission(Permissions::EDIT_SIGN_SHOPS)) {
					$sender->sendMessage(TextFormat::RI . "You do not have permission to edit sign shops!");
					return;
				}
				$session->setShopMode($im = !$session->inShopMode());
				if ($im) {
					$sender->sendMessage(TextFormat::YI . "You are now in sign shop mode! Tap a sign to create or modify an existing sign shop");
				} else {
					$sender->sendMessage(TextFormat::YI . "You are no longer in sign shop mode");
				}
				break;

			case "text":
			case "ft":
				if (!$atIsland) {
					$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
					return;
				}
				$perms = $island->getPermissions()->getPermissionsBy($sender) ?? $island->getPermissions()->getDefaultVisitorPermissions();
				if (!$perms->getPermission(Permissions::EDIT_TEXTS)) {
					$sender->sendMessage(TextFormat::RI . "You do not have permission to edit texts!");
					return;
				}
				$sender->showModal(new IslandTextsUi($island));
				break;

			case "tutorial":
			case "tut":
				SkyBlock::getInstance()->getIslands()->getIslandManager()->gotoIsland($sender, Island::TUTORIAL);
				break;

			case "help":
				$sender->showModal(new CommandHelpUi($sender));
				break;

			case "stp":
				if (!$sender->isStaff()) {
					$sender->sendMessage(TextFormat::RI . "You do not have permission to use this subcommand");
					return;
				}
				$sender->showModal(new VisitAnyIslandUi());
				break;

			case "setlevel":
			case "sl":
				if ($sender->isTier3()) {
					if (count($args) === 0) {
						$sender->sendMessage("/island setlevel [int: level]");
						return;
					}

					$level = (int) $args[0];

					$island->sizeLevel = $level;

					$max = min(ChallengeManager::TOTAL_LEVELS, $level);

					for($i = 1; $i <= $max; $i++){
						$island->getChallengeManager()->getLevelSession($i);
					}

					$island->updateScoreboardLines(false, false, true);
					$sender->sendMessage(TextFormat::GN . "Force set island to level " . $island->getSizeLevel());
				}
				break;
			case "forcelevel":
			case "fl":
				if ($sender->isTier3()) {
					if (count($args) === 0) {
						$island->levelUp($sender, false);
					} else {
						$amount = \intval($args[0]);

						for ($i = 0; $i < $amount; $i++) {
							$island->levelUp($sender, false);
						}
					}

					$sender->sendMessage(TextFormat::GN . "Force leveled up island to level " . $island->getSizeLevel());
				}
				break;

			case "setowner":
			case "so":
				if ($sender->isTier3()) {
					if (count($args) === 0) {
						$sender->sendMessage(TextFormat::RI . "Fatass");
						return;
					}

					($owner = $island->getPermissions()->getOwner())->setPermission(Permissions::OWNER, false);

					$player = Server::getInstance()->getPlayerByPrefix(array_shift($args));
					if(!$player instanceof SkyBlockPlayer){
						$sender->sendMessage(TextFormat::RI . "Lance is a midget");
						return;
					}
					$perms = $island->getPermissions()->getPermissionsBy($player);
					if (is_null($perms)) $perms = $island->getPermissions()->addNewDefaultPermissions($player->getUser());
					
					$p = $perms->getHierarchy();

					foreach (Permissions::DEFAULT_INVITE_PERMISSIONS as $key => $permission) {
						$perms->setPermission($key, true);
					}

					$h = $owner->getHierarchy();
					$owner->setHierarchy($p);
					$perms->setHierarchy($h);

					$island->getPermissions()->updatePermissions($perms);
					$island->getPermissions()->updatePermissions($owner);

					$sender->sendMessage(TextFormat::GN . "Forced owner of island you're on to " . $player->getName());
				}
				break;

			case "sethierarchy":
			case "sh":
				if ($sender->isTier3()) {
					if (count($args) === 0) {
						$sender->sendMessage(TextFormat::RI . "Fatass");
						return;
					}

					($owner = $island->getPermissions()->getOwner())->setPermission(Permissions::OWNER, false);

					$player = Server::getInstance()->getPlayerByPrefix(array_shift($args));
					if(!$player instanceof Player){
						$sender->sendMessage(TextFormat::RI . "Lance is a midget");
						return;
					}
					$perms = $island->getPermissions()->getPermissionsBy($player);
					if (is_null($perms)){
						$sender->sendMessage(TextFormat::RI . "Not an island member!");
						return;	
					}

					if(count($args) === 0){
						$sender->sendMessage(TextFormat::RI . "Must define hierarchy");
						return;
					}
					$h = (int) array_shift($args);
					
					$perms->setHierarchy($h);

					$island->getPermissions()->updatePermissions($perms);

					$sender->sendMessage(TextFormat::GN . "Set hierarchy of " . $player->getName() . " to " . $h);
				}
				break;
			case "ogc":
				if ($sender->isTier3()) {
					if (count($args) === 0) {
						$sender->sendMessage(TextFormat::RI . "Usage: /is ogc <count>");
						return;
					}
					$count = (int) array_shift($args);
					$island->setGenCount($count);
					$sender->sendMessage(TextFormat::GN . "Set this island's gen count to " . $count);
				}
				break;
			case "hc":
				if ($sender->isTier3()) {
					if (count($args) === 0) {
						$sender->sendMessage(TextFormat::RI . "Usage: /is hc <count>");
						return;
					}
					$count = (int) array_shift($args);
					$island->setHopperCount($count);
					$sender->sendMessage(TextFormat::GN . "Set this island's hopper count to " . $count);
				}
				break;
			case "sc":
				if ($sender->isTier3()) {
					if (count($args) === 0) {
						$sender->sendMessage(TextFormat::RI . "Usage: /is sc <count>");
						return;
					}
					$count = (int) array_shift($args);
					$island->setSpawnerCount($count);
					$sender->sendMessage(TextFormat::GN . "Set this island's spawner count to " . $count);
				}
				break;

			case "gennew":
				if ($sender->isSn3ak()) {
					if (count($args) === 0) {
						$sender->sendMessage(TextFormat::RI . "Usage: /is gennew <world name>");
						return;
					}
					SkyBlock::getInstance()->getIslands()->getIslandManager()->generateIslandWorld($name = array_shift($args), 0);
					$sender->sendMessage(TextFormat::GI . "Generated new island world named " . TextFormat::YELLOW . $name);
				}
				break;

			case "addperms":
				if ($sender->isTier3()) {
				}
				break;

			case "forceleave":
				if ($sender->isTier3()) {
				}
				break;

			case "forceperms":
			case "fp":
				if($sender->isTier3()){
					if (!$atIsland) {
						$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
						return;
					}

					$user = $sender->getUser();

					if(isset($args[0])){
						if(is_null(($target = Core::thisServer()->getCluster()->getUser($args[0])))){
							$sender->sendMessage(TextFormat::RI . "Player is not online right now!");
							return;
						}

						$user = $target;
					}

					$perms = $island->getPermissions()->getPermissionsBy($user);

					if(is_null($perms)) $perms = $island->getPermissions()->addNewDefaultPermissions($user);

					foreach(Permissions::DEFAULT_INVITE_PERMISSIONS as $key => $permission) {
						if ($key == Permissions::OWNER) continue;
						$perms->setPermission($key, true);
					}
					$perms->setHierarchy(1000);
					$island->getPermissions()->updatePermissions($perms);

					if($user->getXuid() === $sender->getXuid()){
						$sender->sendMessage(TextFormat::GN . "Forced your permissions to max boi");
					}else{
						$sender->sendMessage(TextFormat::GN . "You forced " . TextFormat::YELLOW . $user->getName() . "'s" . TextFormat::GRAY . " permissions to max boi");
						if(!is_null(($player = $user->getPlayer()))) $player->sendMessage(TextFormat::GN . "Your permissions were forced on the island " . TextFormat::AQUA . $island->getName() . TextFormat::RESET . TextFormat::GRAY . "!");
					}
					return;
				}
				break;

			case "forcesave":

				break;

			case "forcekickmember":
			case "forceremovemember":
			case "fkm":
			case "frm":
				if($sender->isTier3()){
					if(!$atIsland) {
						$sender->sendMessage(TextFormat::RI . "You must be at an island to do this");
						return;
					}

					if(!(isset($args[0]))){
						$sender->sendMessage(TextFormat::RI . "You must enter an island member's name");
						return;
					}

					$member = strtolower($args[0]);
					$player = Server::getInstance()->getPlayerExact($member);
					$removed = false;

					if(is_null($player)){
						/** @var PlayerPermissions $permission */
						foreach($island->getPermissions()->getPermissions() as $permission){
							/** @var User $user */
							$user = $permission->getUser();

							if(strtolower($user->getGamerTag()) === $member){
								$permission->delete();

								$member = $user->getGamertag();
								$removed = true;
								
								unset($island->getPermissions()->permissions[$user->getXuid()]);

								break;
							}
						}
					}else{
						$perms = $island->getPermissions()->getPermissionsBy($player);

						if(is_null($perms)){
							$sender->sendMessage(TextFormat::RN . "That player no longer has permissions on this island.");
							return;
						}

						$island->getPermissions()->removePermissions($perms);
						$removed = true;
					}

					if($removed){
						$sender->sendMessage(TextFormat::GN . "You force removed " . TextFormat::YELLOW . $member . TextFormat::GRAY . " from the island");
					}else{
						$sender->sendMessage(TextFormat::RN . "There was no member on the island with the name " . TextFormat::RED . $member);
					}
				}
				break;
		}
	}
}

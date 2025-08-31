<?php namespace skyblock\islands\ui\manage\permission;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\{
	PlayerPermissions,
	Permissions
};
use skyblock\islands\ui\manage\{
	IslandManageUi,
	IslandMembersUi
};

use core\ui\elements\customForm\{
	Label,
	Input,
	Toggle
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class EditPermissionsUi extends CustomForm{

	public function __construct(public Island $island, public PlayerPermissions $permissions, string $message = "", bool $error = true){
		parent::__construct("Edit permissions");

		$this->addElement(new Label(
			($message === "" ? "" : ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL) .
			"Hierarchy level affects which warps players can access, as well as permissions they're allowed to edit (if 'Edit permissions' is toggled)." . PHP_EOL . PHP_EOL .
			"Ex: Players with hierarchy level 1 can go to warps with hierarchy level 1 and below, players can edit permissions of players below their hierarchy"
		));
		$this->addElement(new Input("Hierarchy level", "1", $permissions->getHierarchy()));

		$this->addElement(new Toggle("Edit blocks", $permissions->getPermission(Permissions::EDIT_BLOCKS)));
		$this->addElement(new Toggle("Edit armor stands", $permissions->getPermission(Permissions::EDIT_ARMOR_STANDS)));
		$this->addElement(new Toggle("Edit item frames", $permissions->getPermission(Permissions::EDIT_ITEM_FRAMES)));
		$this->addElement(new Toggle("Edit spawners", $permissions->getPermission(Permissions::EDIT_SPAWNERS)));
		$this->addElement(new Toggle("Edit generator blocks (Ore generators, dimensional blocks, autominers)", $permissions->getPermission(Permissions::EDIT_GEN_BLOCKS)));
		$this->addElement(new Toggle("Edit ore generator ores", $permissions->getPermission(Permissions::EDIT_ORE_FROM_ORE_GENS)));

		$this->addElement(new Toggle("Open containers", $permissions->getPermission(Permissions::OPEN_CONTAINERS)));
		$this->addElement(new Toggle("Open doors", $permissions->getPermission(Permissions::OPEN_DOORS)));

		$this->addElement(new Toggle("Drop items", $permissions->getPermission(Permissions::DROP_ITEMS)));
		$this->addElement(new Toggle("Pickup items", $permissions->getPermission(Permissions::PICKUP_ITEMS)));
		$this->addElement(new Toggle("Pickup XP", $permissions->getPermission(Permissions::PICKUP_XP)));

		$this->addElement(new Toggle("Throw XP bottles", $permissions->getPermission(Permissions::THROW_XP_BOTTLES)));
		$this->addElement(new Toggle("Throw snowballs", $permissions->getPermission(Permissions::THROW_SNOWBALLS)));
		$this->addElement(new Toggle("Throw ender pearls", $permissions->getPermission(Permissions::THROW_ENDER_PEARLS)));
		$this->addElement(new Toggle("Cast fishing rod", $permissions->getPermission(Permissions::CAST_FISHING_ROD)));

		$this->addElement(new Toggle("Kill spawner mobs", $permissions->getPermission(Permissions::KILL_SPAWNER_MOBS)));

		$this->addElement(new Toggle("Use /sellchest", $permissions->getPermission(Permissions::USE_SELL_CHEST)));
		$this->addElement(new Toggle("Use shop", $permissions->getPermission(Permissions::USE_SHOP)));
		$this->addElement(new Toggle("Use /fly", $permissions->getPermission(Permissions::USE_FLY)));
		$this->addElement(new Toggle("Use warps", $permissions->getPermission(Permissions::USE_WARPS)));
		$this->addElement(new Toggle("Use sign shops", $permissions->getPermission(Permissions::USE_SIGN_SHOPS)));
		$this->addElement(new Toggle("Complete challenges", $permissions->getPermission(Permissions::COMPLETE_CHALLENGES)));

		$this->addElement(new Toggle("Kick visitors", $permissions->getPermission(Permissions::KICK_VISITORS)));
		$this->addElement(new Toggle("Edit/invite members", $permissions->getPermission(Permissions::EDIT_MEMBERS)));
		$this->addElement(new Toggle("Edit default permissions", $permissions->getPermission(Permissions::EDIT_DEFAULT_PERMISSIONS)));
		$this->addElement(new Toggle("Edit block list", $permissions->getPermission(Permissions::EDIT_BLOCK_LIST)));
		$this->addElement(new Toggle("Edit warps", $permissions->getPermission(Permissions::EDIT_WARPS)));
		$this->addElement(new Toggle("Edit warp pads", $permissions->getPermission(Permissions::EDIT_WARP_PADS)));
		$this->addElement(new Toggle("Edit sign shops", $permissions->getPermission(Permissions::EDIT_SIGN_SHOPS)));
		$this->addElement(new Toggle("Edit texts", $permissions->getPermission(Permissions::EDIT_TEXTS)));
		$this->addElement(new Toggle("Move island menu", $permissions->getPermission(Permissions::MOVE_ISLAND_MENU)));
		$this->addElement(new Toggle("Manage island", $permissions->getPermission(Permissions::EDIT_ISLAND)));

		$user = $this->permissions->getUser();
		if($user->getGamertag() !== "Default Invite" && !$this->permissions->isOwner()){
			$this->addElement(new Label("Select the option below if you'd like to remove this member from the island"));
			$this->addElement(new Toggle("Remove member"));
		}
	}

	public function close(Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null){
			return;
		}

		if($this->permissions->getUser()->getGamertag() == "Default Invite"){
			$player->showModal(new IslandManageUi($player, $island));
		}else{
			$player->showModal(new IslandMembersUi($player, $island, true));
		}
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null){
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit this island's permissions!");
			return;
		}

		$user = $this->permissions->getUser();
		$dip = $user->getGamertag() == "Default Invite";
		$perms = ($ip = $island->getPermissions())->getPermissionsBy($user) ??
			($dip ? $ip->getDefaultInvitePermissions() : null);
		if($perms === null){
			$player->sendMessage(TextFormat::RI . "This member no longer exists!");
			return;
		}

		if(!$pp->getPermission(Permissions::EDIT_MEMBERS)){
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit member permissions!");
			return;
		}
		if(!$dip){
			if($pp->getHierarchy() < $perms->getHierarchy()){
				$player->showModal(new IslandMembersUi($player, $island, true, "You can only edit permissions of members with a lower hierarchy than you!"));
				return;	
			}
		}else{
			if(!$pp->getPermission(Permissions::EDIT_DEFAULT_PERMISSIONS)){
				$player->showModal(new IslandManageUi($player, $island, true, "You do not have permission to edit default permissions!"));
				return;
			}
		}

		$kick = $response[35] ?? false;
		if($kick){
			$player->showModal(new KickMemberUi($island, $perms));
			return;
		}

		$hierarchy = (int) $response[1];
		if($hierarchy >= $pp->getHierarchy()){
			$player->showModal(new EditPermissionsUi($island, $perms, "You cannot set this user's hierarchy level higher than your own!"));
			return;
		}
		$perms->setHierarchy($hierarchy);

		$perms->setPermission(Permissions::EDIT_BLOCKS, $response[2]);
		$perms->setPermission(Permissions::EDIT_ARMOR_STANDS, $response[3]);
		$perms->setPermission(Permissions::EDIT_ITEM_FRAMES, $response[4]);
		$perms->setPermission(Permissions::EDIT_SPAWNERS, $response[5]);
		$perms->setPermission(Permissions::EDIT_GEN_BLOCKS, $response[6]);
		$perms->setPermission(Permissions::EDIT_ORE_FROM_ORE_GENS, $response[7]);

		$perms->setPermission(Permissions::OPEN_CONTAINERS, $response[8]);
		$perms->setPermission(Permissions::OPEN_DOORS, $response[9]);
		
		$perms->setPermission(Permissions::DROP_ITEMS, $response[10]);
		$perms->setPermission(Permissions::PICKUP_ITEMS, $response[11]);
		$perms->setPermission(Permissions::PICKUP_XP, $response[12]);

		$perms->setPermission(Permissions::THROW_XP_BOTTLES, $response[13]);
		$perms->setPermission(Permissions::THROW_SNOWBALLS, $response[14]);
		$perms->setPermission(Permissions::THROW_ENDER_PEARLS, $response[15]);
		$perms->setPermission(Permissions::CAST_FISHING_ROD, $response[16]);

		$perms->setPermission(Permissions::KILL_SPAWNER_MOBS, $response[17]);

		$perms->setPermission(Permissions::USE_SELL_CHEST, $response[18]);
		$perms->setPermission(Permissions::USE_SHOP, $response[19]);
		$perms->setPermission(Permissions::USE_FLY, $response[20]);
		$perms->setPermission(Permissions::USE_WARPS, $response[21]);
		$perms->setPermission(Permissions::USE_SIGN_SHOPS, $response[22]);
		$perms->setPermission(Permissions::COMPLETE_CHALLENGES, $response[23]);

		$perms->setPermission(Permissions::KICK_VISITORS, $response[24]);
		$perms->setPermission(Permissions::EDIT_MEMBERS, $response[25]);
		$perms->setPermission(Permissions::EDIT_DEFAULT_PERMISSIONS, $response[26]);
		$perms->setPermission(Permissions::EDIT_BLOCK_LIST, $response[27]);
		$perms->setPermission(Permissions::EDIT_WARPS, $response[28]);
		$perms->setPermission(Permissions::EDIT_WARP_PADS, $response[29]);
		$perms->setPermission(Permissions::EDIT_SIGN_SHOPS, $response[30]);
		$perms->setPermission(Permissions::EDIT_TEXTS, $response[31]);
		$perms->setPermission(Permissions::MOVE_ISLAND_MENU, $response[32]);
		$perms->setPermission(Permissions::EDIT_ISLAND, $response[33]);

		if($dip){
			$island->getPermissions()->updateDefaultInvitePermissions($perms);
			$player->showModal(new IslandManageUi($player, $island, "Default invite permissions have been updated", false));
		}else{
			$island->getPermissions()->updatePermissions($perms);
			$player->showModal(new IslandMembersUi($player, $island, true, "Successfully edited " . $perms->getUser()->getGamertag() . "'s permissions", false));
		}
	}

}
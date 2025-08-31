<?php namespace skyblock\islands\ui\manage\permission;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\{
	PlayerPermissions,
	Permissions
};
use skyblock\islands\ui\manage\IslandMembersUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

class ViewPermissionsUi extends SimpleForm{
	
	public bool $kick = false;

	public function __construct(Player $player, public Island $island, public PlayerPermissions $permissions) {
		/** @var SkyBlockPlayer $player */
		parent::__construct(
			"View permissions",
			"Viewing permissions of " . $permissions->getUser()->getGamertag() . ":" . PHP_EOL . PHP_EOL .

			"Hierarchy: " . $permissions->getHierarchy() . PHP_EOL . PHP_EOL .

			"Edit blocks: " . ($permissions->getPermission(Permissions::EDIT_BLOCKS) ? "YES" : "NO") . PHP_EOL .
			"Edit armor stands: " . ($permissions->getPermission(Permissions::EDIT_ARMOR_STANDS) ? "YES" : "NO") . PHP_EOL .
			"Edit item frames: " . ($permissions->getPermission(Permissions::EDIT_ITEM_FRAMES) ? "YES" : "NO") . PHP_EOL .
			"Edit spawners: " . ($permissions->getPermission(Permissions::EDIT_SPAWNERS) ? "YES" : "NO") . PHP_EOL .
			"Edit generator blocks: " . ($permissions->getPermission(Permissions::EDIT_GEN_BLOCKS) ? "YES" : "NO") . PHP_EOL .
			"Edit ore generator ores: " . ($permissions->getPermission(Permissions::EDIT_ORE_FROM_ORE_GENS) ? "YES" : "NO") . PHP_EOL .

			"Open containers: " . ($permissions->getPermission(Permissions::OPEN_CONTAINERS) ? "YES" : "NO") . PHP_EOL .
			"Open doors: " . ($permissions->getPermission(Permissions::OPEN_DOORS) ? "YES" : "NO") . PHP_EOL . PHP_EOL .

			"Drop items: " . ($permissions->getPermission(Permissions::DROP_ITEMS) ? "YES" : "NO") . PHP_EOL .
			"Pickup items: " . ($permissions->getPermission(Permissions::PICKUP_ITEMS) ? "YES" : "NO") . PHP_EOL .
			"Pickup XP: " . ($permissions->getPermission(Permissions::PICKUP_XP) ? "YES" : "NO") . PHP_EOL . PHP_EOL .

			"Throw XP bottles: " . ($permissions->getPermission(Permissions::THROW_XP_BOTTLES) ? "YES" : "NO") . PHP_EOL .
			"Throw snowballs: " . ($permissions->getPermission(Permissions::THROW_SNOWBALLS) ? "YES" : "NO") . PHP_EOL .
			"Throw ender pearls: " . ($permissions->getPermission(Permissions::THROW_ENDER_PEARLS) ? "YES" : "NO") . PHP_EOL .
			"Cast fishing rod: " . ($permissions->getPermission(Permissions::CAST_FISHING_ROD) ? "YES" : "NO") . PHP_EOL . PHP_EOL .

			"Kill spawner mobs: " . ($permissions->getPermission(Permissions::KILL_SPAWNER_MOBS) ? "YES" : "NO") . PHP_EOL . PHP_EOL .

			"Use /sellchest: " . ($permissions->getPermission(Permissions::USE_SELL_CHEST) ? "YES" : "NO") . PHP_EOL .
			"Use shop: " . ($permissions->getPermission(Permissions::USE_SHOP) ? "YES" : "NO") . PHP_EOL .
			"Use /fly: " . ($permissions->getPermission(Permissions::USE_FLY) ? "YES" : "NO") . PHP_EOL .
			"Use warps: " . ($permissions->getPermission(Permissions::USE_WARPS) ? "YES" : "NO") . PHP_EOL .
			"Use sign shops: " . ($permissions->getPermission(Permissions::USE_SIGN_SHOPS) ? "YES" : "NO") . PHP_EOL .
			"Complete challenges: " . ($permissions->getPermission(Permissions::COMPLETE_CHALLENGES) ? "YES" : "NO") . PHP_EOL . PHP_EOL .

			"Kick visitors: " . ($permissions->getPermission(Permissions::KICK_VISITORS) ? "YES" : "NO") . PHP_EOL .
			"Edit/invite members: " . ($permissions->getPermission(Permissions::EDIT_MEMBERS) ? "YES" : "NO") . PHP_EOL .
			"Edit default permissions: " . ($permissions->getPermission(Permissions::EDIT_DEFAULT_PERMISSIONS) ? "YES" : "NO") . PHP_EOL .
			"Edit block list: " . ($permissions->getPermission(Permissions::EDIT_BLOCK_LIST) ? "YES" : "NO") . PHP_EOL .
			"Edit warps: " . ($permissions->getPermission(Permissions::EDIT_WARPS) ? "YES" : "NO") . PHP_EOL .
			"Edit warp pads: " . ($permissions->getPermission(Permissions::EDIT_WARP_PADS) ? "YES" : "NO") . PHP_EOL .
			"Edit sign shops: " . ($permissions->getPermission(Permissions::EDIT_SIGN_SHOPS) ? "YES" : "NO") . PHP_EOL .
			"Edit texts: " . ($permissions->getPermission(Permissions::EDIT_TEXTS) ? "YES" : "NO") . PHP_EOL .
			"Move island menu: " . ($permissions->getPermission(Permissions::MOVE_ISLAND_MENU) ? "YES" : "NO") . PHP_EOL .
			"Manage island: " . ($permissions->getPermission(Permissions::EDIT_ISLAND) ? "YES" : "NO")
		);

		$perms = $island->getPermissions()->getPermissionsBy($player);
		if(
			$perms !== null &&
			$perms->getPermission(Permissions::KICK_VISITORS) &&
			$perms->getHierarchy() > $permissions->getHierarchy()
		){
			$this->kick = true;
			$this->addButton(new Button("Kick member"));
		}

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$perms = $this->island->getPermissions()->getPermissionsBy($player);
		if($this->kick && $response === 0){
			if(!$perms->getPermission(Permissions::KICK_VISITORS)){
				$player->showModal(new IslandMembersUi($player, $this->island, false, "You do not have permission to kick visitors!"));
				return;
			}
			if($perms->getHierarchy() <= $this->permissions->getHierarchy()){
				$player->showModal(new IslandMembersUi($player, $this->island, false, "This member has an equal or higher hierarchy level then you, preventing you from kicking them"));
				return;
			}
			if (!is_null($pl = $this->permissions->getUser()->getPlayer())) $this->island->kick($pl);
			else $this->island->getPermissions()->removePermissions($this->permissions);
			$player->showModal(new IslandMembersUi($player, $this->island, false, "Member was kicked off the island!", false));
			return;
		}
		$player->showModal(new IslandMembersUi($player, $this->island));
	}

}
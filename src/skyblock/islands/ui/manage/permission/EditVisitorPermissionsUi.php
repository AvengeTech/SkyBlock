<?php namespace skyblock\islands\ui\manage\permission;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\{
	Permissions
};
use skyblock\islands\ui\manage\IslandManageUi;

use core\ui\elements\customForm\{
	Label,
	Input,
	Toggle
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class EditVisitorPermissionsUi extends CustomForm{

	public function __construct(public Island $island, string $message = "", bool $error = true){
		parent::__construct("Edit visitor permissions");

		$permissions = $island->getPermissions()->getDefaultVisitorPermissions();

		$this->addElement(new Label(
			($message === "" ? "" : ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL) .
			"(NOTE: Some permissions aren't available to visitors)" . PHP_EOL . PHP_EOL .

			"Hierarchy level affects which warps visitors can access" . PHP_EOL . PHP_EOL .
			"Ex: Players with hierarchy level 2 can go to warps with hierarchy level 2 and below"
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

		$this->addElement(new Toggle("Use /fly", $permissions->getPermission(Permissions::USE_FLY)));
		$this->addElement(new Toggle("Use warps", $permissions->getPermission(Permissions::USE_WARPS)));
		$this->addElement(new Toggle("Use sign shops", $permissions->getPermission(Permissions::USE_SIGN_SHOPS)));
		$this->addElement(new Toggle("Complete challenges", $permissions->getPermission(Permissions::COMPLETE_CHALLENGES)));

		$this->addElement(new Toggle("Edit warps", $permissions->getPermission(Permissions::EDIT_WARPS)));
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

		$player->showModal(new IslandManageUi($player, $island));
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

		$perms = $island->getPermissions()->getDefaultVisitorPermissions();
		if(!$pp->getPermission(Permissions::EDIT_DEFAULT_PERMISSIONS)){
			$player->showModal(new IslandManageUi($player, $island, true, "You do not have permission to edit default permissions!"));
			return;
		}

		$hierarchy = (int) $response[1];
		if($hierarchy > $pp->getHierarchy()){
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

		$perms->setPermission(Permissions::USE_FLY, $response[18]);
		$perms->setPermission(Permissions::USE_WARPS, $response[19]);
		$perms->setPermission(Permissions::USE_SIGN_SHOPS, $response[20]);
		$perms->setPermission(Permissions::COMPLETE_CHALLENGES, $response[21]);

		$perms->setPermission(Permissions::EDIT_WARPS, $response[22]);

		$island->getPermissions()->updateDefaultVisitorPermissions($perms);
		$player->showModal(new IslandManageUi($player, $island, "Visitor permissions have been updated", false));
	}

}
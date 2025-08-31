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
	IslandMembersUi
};

use core\ui\windows\ModalWindow;
use core\utils\TextFormat;

class KickMemberUi extends ModalWindow{

	public function __construct(public Island $island, public PlayerPermissions $permissions){
		parent::__construct(
			"Remove member",
			"Are you sure you want to remove member " . $permissions->getUser()->getGamertag() . " from this island? They will no longer have access unless the island is public",
			"Remove member", "Go back"
		);
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
		if($response){
			$perms = ($ip = $island->getPermissions())->getPermissionsBy($this->permissions->getUser()) ?? null;
			if($perms === null){
				$player->sendMessage(TextFormat::RI . "This member no longer exists!");
				return;
			}

			if(!$pp->getPermission(Permissions::EDIT_MEMBERS)){
				$player->sendMessage(TextFormat::RI . "You don't have permission to edit member permissions!");
				return;
			}
			if($pp->getHierarchy() <= $perms->getHierarchy()){
				$player->showModal(new IslandMembersUi($player, $island, true, "You can only edit permissions of members with a lower hierarchy than you!"));
				return;
			}
			
			$island->getPermissions()->removePermissions($perms);
			$player->showModal(new IslandMembersUi($player, $island, true, "Member was successfully kicked!", false));
		}else{
			$player->showModal(new IslandMembersUi($player, $island));
		}
	}

}
<?php namespace skyblock\islands\ui\manage;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\{
	Island,
};
use skyblock\islands\permission\Permissions;
use skyblock\islands\ui\manage\invite\InvitePlayerUi;
use skyblock\islands\ui\manage\permission\{
	EditPermissionsUi,
	ViewPermissionsUi
};

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class IslandMembersUi extends SimpleForm{

	public array $permissions = [];

	public function __construct(Player $player, public Island $island, public bool $edit = false, string $message = "", bool $error = true) {
		/** @var SkyBlockPlayer $player */
		parent::__construct(
			"Island members",
			($message === "" ? "" : ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL) .
			"Select an island member to " . ($edit ? "edit" : "view") . " their permissions!"
		);

		$pp = $island->getPermissions()->getPermissionsBy($player) ?? $island->getPermissions()->getDefaultVisitorPermissions();
		if($pp->getPermission(Permissions::EDIT_MEMBERS)){
			if($edit){
				$this->addButton(new Button("Invite member"));
			}else{
				$this->addButton(new Button("Edit members"));
			}
		}
		foreach($island->getPermissions()->getPermissions() as $permission){
			$this->permissions[] = $permission;
			$this->addButton(new Button("[" . $permission->getHierarchy() . "] " . $permission->getUser()->getGamertag()));
		}
		if($pp->getPermission(Permissions::EDIT_MEMBERS) && $edit){
			$this->addButton(new Button("Cancel"));
		}else{
			$this->addButton(new Button("Go back"));
		}
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player) ?? $island->getPermissions()->getDefaultVisitorPermissions();
		if($pp === null){
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit this island's permissions!");
			return;
		}

		if($pp->getPermission(Permissions::EDIT_MEMBERS)){
			if($response == 0){
				if($this->edit){
					if(count($island->getPermissions()->getPermissions()) + SkyBlock::getInstance()->getIslands()->getInviteManager()->getInvitesTo($island) >= $island->getPermissions()->getTotalMembersAllowed()){
						$player->showModal(new IslandMembersUi($player, $island, true, "Max amount of island members reached! (Including total invites sent out)"));
						return;
					}
					$player->showModal(new InvitePlayerUi($island));
				}else{
					$player->showModal(new IslandMembersUi($player, $island, true));
				}
				return;
			}
			$response--;
		}

		$perms = $this->permissions[$response] ?? null;
		if($perms === null){
			if($this->edit){
				$player->showModal(new IslandMembersUi($player, $island));
			}else{
				$player->showModal(new IslandInfoUi($player, $island));
			}
			return;
		}
		$perms = $island->getPermissions()->getPermissionsBy($perms->getUser());
		if($perms === null){
			$player->showModal(new IslandMembersUi($player, $island, $this->edit, "This member no longer exists"));
			return;
		}

		if($pp->getPermission(Permissions::EDIT_MEMBERS) && $this->edit){
			if($perms->getHierarchy() >= $pp->getHierarchy()){
				$player->showModal(new IslandMembersUi($player, $island, true, "You can only edit members with a hierarchy level lower then yours"));
				return;
			}
			if($perms->isOwner()){
				$player->showModal(new IslandMembersUi($player, $island, true, "You can't edit owner permissions!"));
				return;
			}
			$player->showModal(new EditPermissionsUi($island, $perms));
		}else{
			$player->showModal(new ViewPermissionsUi($player, $island, $perms));
		}
	}

}
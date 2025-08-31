<?php namespace skyblock\islands\warp\ui;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\warp\Warp;

use core\ui\windows\ModalWindow;
use core\utils\TextFormat;

class DeleteWarpUi extends ModalWindow{

	public function __construct(public Island $island, public Warp $warp, public int $page){
		parent::__construct(
			"Delete warp",
			"Are you sure you want to delete the warp " . $warp->getName() . TextFormat::RESET . TextFormat::WHITE . "?",
			"Delete warp", "Go back"
		);
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "This island is no longer loaded.");
			return;
		}
		$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if(!$permissions->isOwner() && !$permissions->getPermission(Permissions::EDIT_WARPS)){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, false, "You no longer have permission to edit warps at this island!"));
			return;
		}
		$warp = ($wm = $island->getWarpManager())->getWarp($this->warp->getName());
		if($warp === null){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "This warp no longer exists"));
			return;
		}

		if($warp->getHierarchy() > $permissions->getHierarchy()){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "You no longer have permission to edit this warp!"));
			return;
		}

		if($response){
			$island->getWarpManager()->removeWarp($warp->getName());
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Warp successfully deleted!", false));
		}else{
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true));
		}
	}

}
<?php namespace skyblock\islands\warp\ui;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\ui\manage\IslandInfoUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class IslandWarpsUi extends SimpleForm{
	
	const PER_PAGE = 8; //todo: PAGES

	public array $warps = [];

	public function __construct(Player $player, public Island $island, public int $page = 1, public bool $edit = false, string $message = "", bool $error = true){
		parent::__construct($edit ? "Editing island warps" : "Island warps",
			($message === "" ? "" : ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL) .
			"Tap a warp to teleport to it!"
		);
		$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

		if($edit){
			$this->addButton(new Button("Add warp"));
		}else{
			if($permissions->isOwner() || $permissions->getPermission(Permissions::EDIT_WARPS)){
				$this->addButton(new Button("Edit warps"));
			}
		}
		foreach($island->getWarpManager()->getWarpsFor($permissions->getHierarchy()) as $warp){
			$this->warps[] = $warp;
			$this->addButton(new Button("[" . $warp->getHierarchy() . "] " . $warp->getName()));
		}
		if($edit){
			$this->addButton(new Button("Cancel"));
		}else{
			$this->addButton(new Button("Go back"));
		}
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "This island is no longer loaded.");
			return;
		}
		$wm = $island->getWarpManager();

		$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if($this->edit){
			if(!$permissions->isOwner() && !$permissions->getPermission(Permissions::EDIT_WARPS)){
				$player->showModal(new IslandWarpsUi($player, $island, $this->page, false, "You no longer have permission to edit warps at this island!"));
				return;
			}
			if($response == 0){
				if(count($wm->getWarps()) >= $wm->getWarpLimit()){
					$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "This island has reached it's warp limit! You earn 3 new warps per each island level"));
					return;
				}
				$player->showModal(new AddWarpUi($player, $island, $this->page));
				return;
			}
			$warp = $this->warps[$response - 1] ?? null;
			if($warp === null){
				$player->showModal(new IslandWarpsUi($player, $island, $this->page));
				return;
			}
			$warp = $wm->getWarp($warp->getName());
			if($warp === null){
				$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "That warp no longer exists"));
				return;
			}
			if($warp->getHierarchy() > $permissions->getHierarchy()){
				$player->showModal(new IslandWarpsUi($player, $island, 1, false, "You no longer have access to this warp!"));
				return;
			}
			$player->showModal(new EditWarpUi($island, $warp, $this->page));
		}else{
			if($permissions->isOwner() || $permissions->getPermission(Permissions::EDIT_WARPS)){
				if($response == 0){
					$player->showModal(new IslandWarpsUi($player, $island, $this->page, true));
					return;
				}
			}
			$warp = $this->warps[$response - 1] ?? null;
			if($warp !== null){
				if(!$permissions->getPermission(Permissions::USE_WARPS)){
					$player->showModal(new IslandInfoUi($player, $island, false, "You no longer have access to this island's warps"));
					return;
				}
				$warp = $island->getWarpManager()->getWarp($warp->getName());
				if($warp === null){
					$player->showModal(new IslandWarpsUi($player, $island, $this->page, false, "That warp no longer exists"));
					return;
				}
				if($warp->getHierarchy() > $permissions->getHierarchy()){
					$player->showModal(new IslandWarpsUi($player, $island, 1, false, "You no longer have access to this warp!"));
					return;
				}
				$warp->teleportTo($player);
				return;
			}
			$player->showModal(new IslandInfoUi($player, $island));
		}
	}

}
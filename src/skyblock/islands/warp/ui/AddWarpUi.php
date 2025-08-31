<?php namespace skyblock\islands\warp\ui;

use pocketmine\math\Vector3;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\warp\Warp;

use core\ui\elements\customForm\{
	Label,
	Input,
	Toggle
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class AddWarpUi extends CustomForm{

	public function __construct(Player $player, public Island $island, public int $page){
		parent::__construct("Add warp");

		$this->addElement(new Label("Warp information"));
		$this->addElement(new Input("Name", "warp name"));
		$this->addElement(new Input("Description", "warp description"));

		$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		$this->addElement(new Label("Island members with this hierarchy level or above will have access to this warp"));
		$this->addElement(new Input("Hierarchy level", "number", $permissions->getHierarchy()));

		$this->addElement(new Toggle("Check this box if you'd like to make a warp with the above information!"));
		$this->addElement(new Label("After filling in the above information, press submit!"));
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

		$confirm = $response[5];
		if(!$confirm){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true));
			return;
		}

		$name = $response[1];
		$description = $response[2];

		$hierarchy = (int) $response[4];
		if($hierarchy > $permissions->getHierarchy()){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Cannot set warp hierarchy level higher than your own!"));
			return;
		}

		if(strlen($name) < 1){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Warp name must be at least 1 character"));
			return;
		}
		if(strlen($name) > Warp::LIMIT_NAME){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Warp name too long! Must be within " . Warp::LIMIT_NAME . " characters long"));
			return;
		}
		if($island->getWarpManager()->getWarp($name) !== null){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Warp with this name already exists, please choose another name"));
			return;
		}
		foreach($island->getPlayers() as $pl){
			if(($ses = $pl->getGameSession()->getIslands())->inWarpMode() && $ses->getWarpMode()->getName() == $name){
				$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Someone on this island is already creating a warp with that exact name, please choose another one"));
				return;
			}
		}
		if(strlen($description) > Warp::LIMIT_DESCRIPTION){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Warp description too long! Must be within " . Warp::LIMIT_DESCRIPTION . " characters long"));
			return;
		}

		$warp = new Warp($island->getWarpManager(), time(), $name, $description, $hierarchy, new Vector3(0, 0, 0), -1);
		$player->getGameSession()->getIslands()->setWarpMode($warp);
		$player->sendMessage(TextFormat::GI . "You are now in warp creation mode. Tap the block you'd like this warp to spawn players on top of");
	}

}
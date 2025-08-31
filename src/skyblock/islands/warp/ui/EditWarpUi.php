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

class EditWarpUi extends CustomForm{

	public function __construct(public Island $island, public Warp $warp, public int $page){
		parent::__construct("Editing warp: " . $warp->getName());
		
		$this->addElement(new Label("Warp information"));
		$this->addElement(new Input("Name", "warp name", $warp->getName()));
		$this->addElement(new Input("Description", "warp description", $warp->getDescription()));
		
		$this->addElement(new Label("Hierarchy - Players with this hierarchy or above can access this warp"));
		$this->addElement(new Input("Hierarchy", "number", $warp->getHierarchy()));

		$this->addElement(new Label("Position"));
		$this->addElement(new Input("X", "X coordinate", $warp->getLocation()->getX()));
		$this->addElement(new Input("Y", "Y coordinate", $warp->getLocation()->getY()));
		$this->addElement(new Input("Z", "Z coordinate", $warp->getLocation()->getZ()));
		$this->addElement(new Input("Rotation (Set to -1 to disable)", "0-360", $warp->getLocation()->getYaw()));
		$this->addElement(new Toggle("Delete warp"));
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
		
		$delete = $response[10];
		if($delete){
			$player->showModal(new DeleteWarpUi($island, $warp, $this->page));
			return;
		}
		
		$name = $response[1];
		$description = $response[2];
		
		$hierarchy = (int) $response[4];
		if($hierarchy > $permissions->getHierarchy()){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Cannot set warp hierarchy higher then your own!"));
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
		if($name !== $this->warp->getName() && $wm->getWarp($name) !== null){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Warp with this name already exists! Please choose another name"));
			return;
		}
		if(strlen($description) > Warp::LIMIT_DESCRIPTION){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Warp description too long! Must be within " . Warp::LIMIT_DESCRIPTION . " characters long"));
			return;
		}

		$x = round((float) $response[6], 1);
		$y = round((float) $response[7], 1);
		$z = round((float) $response[8], 1);
		if(!$island->inZone(new Vector3($x, $y, $z))){
			$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Coordinates provided are not within the island's zone!"));
			return;
		}
		
		$yaw = (int) $response[9];

		$wm->removeWarp($this->warp->getName());
		
		$warp->setName($name);
		$warp->setDescription($description);
		$warp->updateLocation($x, $y, $z, $yaw);
		$warp->setHierarchy($hierarchy);
		$wm->addWarp($warp);

		$player->showModal(new IslandWarpsUi($player, $island, $this->page, true, "Successfully edited warp!", false));
	}
	
}
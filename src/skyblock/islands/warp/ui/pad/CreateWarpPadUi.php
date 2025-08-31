<?php namespace skyblock\islands\warp\ui\pad;

use pocketmine\math\Vector3;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\warp\WarpPad;
use skyblock\islands\warp\block\StonePressurePlate;

use core\ui\elements\customForm\{
	Label,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class CreateWarpPadUi extends CustomForm{
	
	public array $warps = [];

	public function __construct(Player $player, public Island $island, public Vector3 $position, string $message = "", bool $error = true){
		parent::__construct("Create warp pad");

		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Select which warp you'd like this warp pad to redirect to!"
		));

		$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		$warps = $island->getWarpManager()->getWarpsFor($permissions->getHierarchy());
		$text = [];
		foreach($warps as $warp){
			$this->warps[] = $warp;
			$text[] = $warp->getName();
		}
		$this->addElement(new Dropdown("Warps", $text));
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "This island is no longer loaded.");
			return;
		}
		$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if(!$permissions->isOwner() && !$permissions->getPermission(Permissions::EDIT_WARP_PADS)){
			$player->sendMessage(TextFormat::RI . "You can no longer edit warp pads on this island!");
			return;
		}

		if(!$player->getPosition()->getWorld()->getBlock($this->position) instanceof StonePressurePlate){
			$player->sendMessage(TextFormat::RI . "Warp pad no longer placed.");
			return;
		}

		$warp = $this->warps[$response[1]] ?? null;
		if($warp === null){
			return;
		}
		$warp = $island->getWarpManager()->getWarp($warp->getName());
		if($warp === null){
			$player->showModal(new CreateWarpPadUi($player, $island, $this->position, "Warp no longer exists!"));
			return;
		}
		$hierarchy = $warp->getHierarchy();
		if($hierarchy > $permissions->getHierarchy()){
			$player->showModal(new CreateWarpPadUi($player, $island, $this->position, "You no longer have permission to use this warp!"));
			return;
		}

		$wm = $island->getWarpManager();
		$wm->addWarpPad(new WarpPad($wm, $warp->getName(), $this->position));
		$player->sendMessage(TextFormat::GI . "Your warp pad is now setup!");
	}

}
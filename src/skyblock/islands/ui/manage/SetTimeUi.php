<?php namespace skyblock\islands\ui\manage;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;

use core\ui\elements\customForm\{
	Label,
	Input
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class SetTimeUi extends CustomForm{

	public function __construct(public Island $island, string $message = "", bool $error = true) {
		/** @var SkyBlockPlayer $player */
		parent::__construct("Set island time");
		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Type the island time below! (Set to -1 to start the time again)"
		));
		$this->addElement(new Input("Time", "", $island->getTime()));
	}

	public function handle($response, Player $player) {
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
		if(!$pp->getPermission(Permissions::EDIT_ISLAND)){
			$player->showModal(new IslandInfoUi($player, $island, false, "You don't have permission to edit this island!"));
			return;
		}

		$time = (int) $response[1];
		$island->setTime($time);
		$player->showModal(new IslandManageUi($player, $island, "Successfully edited the island's time", false));
	}

}
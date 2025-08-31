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

class EditNameUi extends CustomForm{

	public function __construct(public Island $island, string $message = "", bool $error = true){
		parent::__construct("Edit island name");
		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Enter your new island name below"
		));
		$this->addElement(new Input("Name", "", $island->getName()));
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
		if(!$pp->getPermission(Permissions::EDIT_ISLAND)){
			$player->showModal(new IslandInfoUi($player, $island, false, "You don't have permission to edit this island!"));
			return;
		}

		$name = $response[1];
		if(strlen($name) < 1 || strlen($name) > Island::LIMIT_NAME){
			$player->showModal(new EditNameUi($island, "Island name must be between 1-" . Island::LIMIT_NAME . " characters"));
			return;
		}

		$island->setName($name);
		$player->showModal(new IslandManageUi($player, $island, "Successfully edited the island's name", false));
	}

}
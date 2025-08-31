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

class EditDescriptionUi extends CustomForm{

	public function __construct(public Island $island, string $message = "", bool $error = true){
		parent::__construct("Edit island description");
		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Enter your new island description below"
		));
		$this->addElement(new Input("Description", "", $island->getDescription()));
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

		$description = $response[1];
		if(strlen($description) < 1 || strlen($description) > Island::LIMIT_DESCRIPTION){
			$player->showModal(new EditNameUi($island, "Island description must be between 1-" . Island::LIMIT_DESCRIPTION . " characters"));
			return;
		}

		$island->setDescription($description);
		$player->showModal(new IslandManageUi($player, $island, "Successfully edited the island's description", false));
	}

}
<?php namespace skyblock\islands\text\ui;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\text\{
	Text,
};

use core\ui\windows\ModalWindow;

use core\utils\TextFormat;

class ConfirmDeleteTextUi extends ModalWindow{

	public function __construct(public Island $island, public Text $text, public bool $back){
		parent::__construct(
			"Delete text?",
			"Are you sure you'd like to delete this text?",
			"Delete",
			"Go back"
		);
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$island = $player->getGameSession()->getIslands()->getIslandAt();
		if($island === null){
			$player->sendMessage(TextFormat::RI . "You are not at an island!");
			return;
		}
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if(!$perm->getPermission(Permissions::EDIT_TEXTS)){
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit texts on this island!");
			return;
		}

		if(!$this->text->verify($island)){
			$player->showModal(new IslandTextsUi($island, "This text no longer exists!", true, $this->back));
			return;
		}

		if($response){
			$this->text->getTextManager()->removeText($this->text->getCreated());
			$player->showModal(new IslandTextsUi($island, "Successfully deleted text!", false, $this->back));
		}else{
			$player->showModal(new EditTextUi($island, $this->text));
		}
	}

}

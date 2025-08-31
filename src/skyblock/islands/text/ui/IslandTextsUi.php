<?php namespace skyblock\islands\text\ui;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\ui\manage\IslandInfoUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

use core\utils\TextFormat;

class IslandTextsUi extends SimpleForm{

	public array $texts = [];

	public function __construct(public Island $island, string $message = "", bool $error = true, public bool $back = false){
		parent::__construct(
			"Island texts",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Select an option below!"
		);
		$this->addButton(new Button("Add text"));
		foreach($island->getTextManager()->getTexts() as $text){
			$this->texts[] = $text;
			$this->addButton(new Button(
				$text->getShortName() . TextFormat::RESET . TextFormat::DARK_GRAY . PHP_EOL .
				"Tap to modify"
			));
		}
		if($back) $this->addButton(new Button("Go back"));
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
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit floating texts on this island!");
			return;
		}

		if($response === 0){
			if(count(($tm = $island->getTextManager())->getTexts()) > $tm->getTextLimit()){
				$player->showModal(new IslandTextsUi($island, "This island already has the max number of floating texts for it's level!"));
				return;
			}
			$player->showModal(new CreateTextUi($island));
			return;
		}
		$text = $this->texts[$response - 1] ?? null;
		if($text === null){
			$player->showModal(new IslandInfoUi($player, $island));
			return;
		}
		if(!$text->verify($island)){
			$player->showModal(new IslandTextsUi($island, "This text no longer exists!"));
			return;
		}
		$player->showModal(new EditTextUi($island, $text));
	}

}

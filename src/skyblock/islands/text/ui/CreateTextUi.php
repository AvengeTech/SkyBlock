<?php namespace skyblock\islands\text\ui;

use pocketmine\player\Player;
use pocketmine\world\Position;

use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\text\{
	Text,
	TextManager
};

use core\ui\elements\customForm\{
	Label,
	Input
};
use core\ui\windows\CustomForm;

use core\utils\TextFormat;

class CreateTextUi extends CustomForm{

	public function __construct(public Island $island, string $message = "", bool $error = true, public bool $back = false){
		parent::__construct("Create text");
		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Your new text will be created where you're standing." . ($back ? " (TIP: Use /is ft while standing where you'd like your text to be!)" : ""). PHP_EOL . PHP_EOL .
			"Type what you'd like your text to say below!"
		));
		$this->addElement(new Input("Text", "your text here!"));
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
		if(count(($tm = $island->getTextManager())->getTexts()) > $tm->getTextLimit()){
			$player->showModal(new IslandTextsUi($island, "This island already has the max number of floating texts for it's level!"));
			return;
		}

		$text = $response[1];
		if(strlen($text) > TextManager::TEXT_MAX_SIZE){
			$player->showModal(new CreateTextUi($island, "Text must be under " . TextManager::TEXT_MAX_SIZE . " characters long!", true, $this->back));
			return;
		}

		$pos = $player->getPosition();
		$x = round($pos->getX() * 2) / 2;
		$z = round($pos->getZ() * 2) / 2;
		$pos = new Position($x, round($pos->getY(), 1), $z, $pos->getWorld());

		$newText = new Text(
			$tm,
			time(),
			$text,
			$pos
		);
		$newText->setChanged();
		$tm->addText($newText);
		$newText->init();

		$player->showModal(new IslandTextsUi($island, "Successfully created a new text!", false, $this->back));
	}

}

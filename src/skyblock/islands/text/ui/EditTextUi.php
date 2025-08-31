<?php namespace skyblock\islands\text\ui;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\text\{
	Text,
	TextManager
};

use core\ui\elements\customForm\{
	Label,
	Input,
	Toggle
};
use core\ui\windows\CustomForm;

use core\utils\TextFormat;

class EditTextUi extends CustomForm{

	public function __construct(public Island $island, public Text $text, string $message = "", bool $error = true, public bool $back = false){
		parent::__construct("Edit text");
		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Current formatted text: " . $text->getFormattedText() . TextFormat::RESET . TextFormat::WHITE . PHP_EOL . PHP_EOL .
			"Enter the text you'd like to display!"
		));
		$this->addElement(new Input("Text", "your text here!", $text->getText()));
		$this->addElement(new Label("The position of your text can be modified below. (X and Z round to nearest 0.5)"));
		$this->addElement(new Toggle("Move to current position"));
		$this->addElement(new Input("X", "X coordinate", $text->getPosition()->getX()));
		$this->addElement(new Input("Y", "Y coordinate", $text->getPosition()->getY()));
		$this->addElement(new Input("Z", "Z coordinate", $text->getPosition()->getZ()));
		$this->addElement(new Label("Check the option below if you'd like to delete this text."));
		$this->addElement(new Toggle(TextFormat::EMOJI_DENIED . " Delete"));
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
			$player->showModal(new IslandTextsUi($island, "This text no longer exists!"));
			return;
		}

		if($response[8]){
			$player->showModal(new ConfirmDeleteTextUi($island, $this->text, $this->back));
			return;
		}
		$text = $response[1];
		if(strlen($text) > TextManager::TEXT_MAX_SIZE){
			$player->showModal(new EditTextUi($island, $this->text, "Text must be under " . TextManager::TEXT_MAX_SIZE . " characters long!", true, $this->back));
			return;
		}
		$this->text->setText($text);

		if($response[3]){
			$x = $player->getPosition()->getX();
			$y = $player->getPosition()->getY();
			$z = $player->getPosition()->getZ();
		}else{
			$x = (float) min(20000, max(-20000, $response[4]));
			$y = (float) min(20000, max(-20000, $response[5]));
			$z = (float) min(20000, max(-20000, $response[6]));
		}
		$x = round($x * 2) / 2;
		$y = round($y, 1);
		$z = round($z * 2) / 2;

		$this->text->updatePosition($x, $y, $z);

		$player->showModal(new IslandTextsUi($island, "Successfully edited text!", false, $this->back));
	}

}

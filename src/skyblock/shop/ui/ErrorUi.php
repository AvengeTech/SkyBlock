<?php namespace skyblock\shop\ui;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use skyblock\{
	SkyBlockPlayer
};

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

class ErrorUi extends SimpleForm{
	
	public function __construct(public $previous = null, $errormessage = "Unknown error occurred.", $errortop = "Error", public bool $back = false){
		parent::__construct(TextFormat::RED . TextFormat::BOLD . $errortop, TextFormat::RED . $errormessage);
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if($this->previous !== null){
			$player->showModal($this->previous);
			return;
		}
		$player->showModal(new ShopUi($player, $this->back));
	}

}
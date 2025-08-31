<?php namespace skyblock\islands\ui\access;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\{
	Island,
};
use skyblock\islands\ui\IslandsUi;
use skyblock\islands\ui\manage\IslandInfoUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class PublicIslandsUi extends SimpleForm{

	public array $islands = [];
	
	public function __construct(Player $player, array $islands = [], string $error = ""){
		parent::__construct("Public islands", ($error !== "" ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "Select which island you'd like to travel to!");
		foreach($islands as $island){
			if(!$island->isBlocked($player)){
				$this->islands[] = $island;
				$this->addButton(new Button($island->getName() . PHP_EOL . "[" . $island->getPermissions()->getOwner()->getUser()->getGamertag() . "]"));
			}
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = $this->islands[$response] ?? null;
		if($island instanceof Island){
			$player->showModal(new IslandInfoUi($player, $island, true));
			return;
		}
		$player->showModal(new IslandsUi($player));
	}

}
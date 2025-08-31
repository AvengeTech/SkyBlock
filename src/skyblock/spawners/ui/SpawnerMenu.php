<?php namespace skyblock\spawners\ui;

use pocketmine\player\Player;

use core\ui\windows\SimpleForm;

class SpawnerMenu extends SimpleForm{

	public function __construct(Player $player){
		parent::__construct("Spawner Menu", "");
		// $session = SkyBlock::getInstance()->getSpawners()->getSessionManager()->getSession($player);
	}

	public function handle($response, Player $player){
		if($response == 0){

		}
	}

}
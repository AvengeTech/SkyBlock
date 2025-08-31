<?php

namespace skyblock\pets\command;

use core\AtPlayer;
use core\utils\TextFormat as TF;
use core\command\type\CoreCommand;
use skyblock\pets\uis\MyPetsUI;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class MyPets extends CoreCommand {

	public function __construct(private SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["mp"]);
	}

	/** @param SkyBlockPlayer $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$session = $sender->getGameSession()->getPets();
		if(empty($session->getPets())){
			$sender->sendMessage(TF::RI . "You do not have any pets!");
			return;
		}
		$sender->showModal(new MyPetsUI($sender));
	}
}
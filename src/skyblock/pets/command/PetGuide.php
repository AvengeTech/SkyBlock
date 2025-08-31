<?php

namespace skyblock\pets\command;

use core\AtPlayer;
use core\utils\TextFormat as TF;
use core\command\type\CoreCommand;
use skyblock\pets\uis\guide\PetGuideUI;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class PetGuide extends CoreCommand {

	public function __construct(private SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/** @param SkyBlockPlayer $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new PetGuideUI());
	}
}
<?php

namespace skyblock\pets\uis;

use core\AtPlayer;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat as TF;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\pets\Structure;
use skyblock\SkyBlockPlayer;

class MyPetsUI extends SimpleForm{

	public function __construct(
		private SkyBlockPlayer $player,
		private string $label = ""
	){
		$session = $player->getGameSession()->getPets();
		$active = $session->getActivePet();

		$label .= "List of Pets\n";
		$label .= "Unlocked Pets: " . TF::AQUA . count($session->getPets()) . "/" . count(Structure::PETS) . "\n" . TF::RESET;
		$label .= "Active Pet: " . TF::GRAY . (is_null($active) ? "None" : $active->getPetData()->getDefaultName() . " Pet\n");

		parent::__construct("Your Pets", $label);

		foreach($session->getPets() as $pet){
			$button = TF::BOLD . ED::rarityColor($pet->getRarity()) . $pet->getDefaultName() . " Pet\n";
			$button .= TF::RESET . TF::AQUA . "Level " . $pet->getLevel();

			$this->addButton(new Button($button));
		}
	}

	/** @param SkyBlockPlayer $player */
	public function handle($response, AtPlayer $player){
		$session = $player->getGameSession()->getPets();

		$pets = array_values($session->getPets());
		$pet = $pets[$response];

		$player->showModal(new SelectPetUI($player, $pet));
	}
}
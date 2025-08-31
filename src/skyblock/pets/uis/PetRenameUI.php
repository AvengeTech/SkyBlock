<?php

namespace skyblock\pets\uis;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\pets\types\PetData;
use skyblock\SkyBlockPlayer;

class PetRenameUI extends CustomForm{

	public function __construct(
		private PetData $petData,
		string $label = ""
	){
		parent::__construct(TF::BOLD . TF::GOLD . "Pet Rename");

		$label .= TF::GRAY . "Customize your pet's name below. Your pet's name length must be within " . TF::YELLOW . "3" . TF::GRAY . " to " . TF::YELLOW . "15" . TF::GRAY . " characters.";

		$this->addElement(new Label($label));
		$this->addElement(new Input("Enter Name", "My Pet", $petData->getName()));
	}

	/** @param SkyBlockPlayer $player */
	public function handle($response, AtPlayer $player){
		$name = $response[1];

		if(empty(trim($name, "§")) || $name === $this->petData->getName()){
			return $player->showModal(new SelectPetUI($player, $this->petData));
		}

		$length = strlen($name) - (substr_count($name, "§") * 2);

		if($length < 3 || $length > 15){
			return $player->showModal(new self($this->petData, TF::RED . "Your pet's name length must be within " . TF::YELLOW . "3" . TF::GRAY . " to " . TF::YELLOW . "15" . TF::GRAY . " characters.\n\n"));
		}

		$this->petData->setName($name);

		if(
			!is_null(($gs = $player->getGameSession())) &&
			!is_null(($active = $gs->getPets()->getActivePet())) &&
			!is_null(($petData = $active->getPetData())) &&
			$petData->getIdentifier() === $this->petData->getIdentifier()
		){
			$active->updateNameTag();
		}

		$player->showModal(new SelectPetUI($player, $this->petData, TF::GREEN . "You renamed this pet.\n\n"));
	}
}
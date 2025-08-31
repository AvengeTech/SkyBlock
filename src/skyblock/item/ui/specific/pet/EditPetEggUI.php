<?php

namespace skyblock\item\ui\specific\pet;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\item\inventory\specific\pet\EggSelectionInventory;
use skyblock\pets\item\PetEgg;
use skyblock\pets\Structure;
use skyblock\pets\types\PetData;

class EditPetEggUI extends CustomForm{

	private PetData $petData;

	public function __construct(
		private PetEgg $item,
		string $label = ""
	){
		parent::__construct("Edit Pet Egg");

		$label .= "Edit the pet egg data below.";

		$this->addElement(new Label($label));

		$this->petData = $petData = new PetData($item->getIdentifier(), Structure::PETS[$item->getIdentifier()][Structure::DATA_NAME]);

		$this->addElement(new Input("Name", $petData->getDefaultName(), $petData->getDefaultName()));
		$this->addElement(new Input("Level", 1, 1));
		$this->addElement(new Input("XP", 0, 0));
		$this->addElement(new Input("Energy", $petData->getMaxEnergy(), $petData->getMaxEnergy()));
	}

	public function handle($response, AtPlayer $player){
		for($i = 1; $i < 5; $i++){
			if(empty(trim($response[$i])) && $response[3] != 0){
				return $player->showModal(new self($this->item, TF::RED . "Input can not be empty.\n\n"));
			}
		}

		$name = $response[1];
		$length = strlen($name) - (substr_count($name, "§") * 2);

		if($length < 3 || $length > 15){
			return $player->showModal(new self($this->item, TF::RED . "Your pet's name length must be within " . TF::YELLOW . "3" . TF::GRAY . " to " . TF::YELLOW . "15" . TF::GRAY . " characters.\n\n"));
		}

		$level = $response[2];

		if($level > $this->petData->getMaxLevel()){
			return $player->showModal(new self($this->item, TF::RED . "Your pet's level must be less than or equal to " . TF::YELLOW . $this->petData->getMaxLevel() . TF::GRAY . ".\n\n"));
		}

		$xp = $response[3];

		if($xp > $this->petData->getRequiredXp() - 1){
			return $player->showModal(new self($this->item, TF::RED . "Your pet's xp must be less than or equal to " . TF::YELLOW . ($this->petData->getRequiredXp() - 1) . TF::GRAY . ".\n\n"));
		}

		$energy = $response[4];

		if($energy > $this->petData->getMaxEnergy()){
			return $player->showModal(new self($this->item, TF::RED . "Your pet's energy must be less than or equal to " . TF::YELLOW . $this->petData->getMaxEnergy() . TF::GRAY . ".\n\n"));
		}

		$this->petData
		->setName($name)
		->setLevel($level)
		->setXp($xp)
		->setEnergy($energy);

		$this->item->setPetData($this->petData)->init();

		$player->getInventory()->addItem($this->item);
		$player->setCurrentWindow(new EggSelectionInventory($this->item->getCount()));
	}
}
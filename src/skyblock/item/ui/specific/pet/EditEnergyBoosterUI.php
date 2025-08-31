<?php

namespace skyblock\item\ui\specific\pet;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\item\inventory\SpecialItemsInventory;
use skyblock\pets\item\EnergyBooster;

class EditEnergyBoosterUI extends CustomForm{

	public function __construct(
		private EnergyBooster $item,
		string $label = ""
	){
		parent::__construct("Edit Energy Booster");

		$label .= "Edit the pet feed data below.";

		$this->addElement(new Label($label));
		$this->addElement(new Input("Rarity", "1-5", "1"));
		$this->addElement(new Input("Energy", "-1", "-1"));
	}

	public function handle($response, AtPlayer $player){
		for($i = 1; $i < 2; $i++){
			if(empty(trim($response[$i]))){
				return $player->showModal(new self($this->item, TF::RED . "Input can not be empty.\n\n"));
			}
		}

		$rarity = $response[1];

		if(!is_numeric($rarity)){
			return $player->showModal(new self($this->item, TF::RED . "Rarity must be numeric.\n\n"));
		}

		$rarity = intval($rarity);

		if($rarity < 1 || $rarity > 5){
			return $player->showModal(new self($this->item, TF::RED . "Rarity must be within 1-5.\n\n"));
		}

		$energy = $response[2];

		if(!is_numeric($energy)){
			return $player->showModal(new self($this->item, TF::RED . "Energy must be numeric.\n\n"));
		}

		$energy = intval($energy);

		if($energy < -1 || $energy > 1000){
			return $player->showModal(new self($this->item, TF::RED . "Energy must be within 1-1000.\n\n"));
		}

		$item = clone $this->item;
		$item->setup($rarity, $energy)->init();

		if(!$player->getInventory()->canAddItem($item)){
			return $player->sendMessage(TF::RI . "Item can not be added to inventory\n\n");
		}

		$player->getInventory()->addItem($item);
		$player->setCurrentWindow(new SpecialItemsInventory(0));
	}
}
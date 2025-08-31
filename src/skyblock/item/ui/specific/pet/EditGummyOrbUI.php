<?php

namespace skyblock\item\ui\specific\pet;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\item\inventory\SpecialItemsInventory;
use skyblock\pets\item\GummyOrb;

class EditGummyOrbUI extends CustomForm{

	public function __construct(
		private GummyOrb $item,
		string $label = ""
	){
		parent::__construct("Edit Gummy Orb");

		$label .= "Edit the pet feed data below.";

		$this->addElement(new Label($label));
		$this->addElement(new Input("Rarity", "1-5", "1"));
		$this->addElement(new Input("EXP", "-1", "-1"));
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

		$exp = $response[2];

		if(!is_numeric($exp)){
			return $player->showModal(new self($this->item, TF::RED . "EXP must be numeric.\n\n"));
		}

		$exp = intval($exp);

		if($exp < -1 || $exp > 1000){
			return $player->showModal(new self($this->item, TF::RED . "EXP must be within 1-1000.\n\n"));
		}

		$item = clone $this->item;
		$item->setup($rarity, $exp)->init();

		if(!$player->getInventory()->canAddItem($item)){
			return $player->sendMessage(TF::RI . "Item can not be added to inventory\n\n");
		}

		$player->getInventory()->addItem($item);
		$player->setCurrentWindow(new SpecialItemsInventory());
	}
}
<?php

namespace skyblock\item\ui\specific\essence;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\elements\customForm\Toggle;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\item\EssenceOfSuccess;
use skyblock\item\inventory\specific\essence\EssenceSelectionInventory;

class EditSuccessUI extends CustomForm{

	public function __construct(
		private EssenceOfSuccess $essence, 
		string $label = ""
	){
		parent::__construct("Edit Success");

		$label .= "Edit the essence data below.";

		$this->addElement(new Label($label));
		$this->addElement(new Input("Rarity", "1-5", "1"));
		$this->addElement(new Input("Cost", "-1", "-1"));
		$this->addElement(new Input("Percent", "-1", "-1"));
		$this->addElement(new Toggle("isRaw", true));
	}

	public function handle($response, AtPlayer $player){
		for($i = 1; $i < 3; $i++){
			if(empty(trim($response[$i]))){
				return $player->showModal(new self($this->essence, TF::RED . "Input can not be empty.\n\n"));
			}
		}

		$rarity = $response[1];

		if(!is_numeric($rarity)){
			return $player->showModal(new self($this->essence, TF::RED . "Rarity must be numeric.\n\n"));
		}

		$rarity = intval($rarity);

		if($rarity < 1 || $rarity > 5){
			return $player->showModal(new self($this->essence, TF::RED . "Rarity must be within 1-5.\n\n"));
		}

		$cost = $response[2];

		if(!is_numeric($cost)){
			return $player->showModal(new self($this->essence, TF::RED . "Cost must be numeric.\n\n"));
		}

		$cost = intval($cost);

		if($cost < -1 || $cost > 1000){
			return $player->showModal(new self($this->essence, TF::RED . "Cost must be within 1-1000.\n\n"));
		}

		$percent = $response[3];

		if(!is_numeric($percent)){
			return $player->showModal(new self($this->essence, TF::RED . "Percent must be numeric.\n\n"));
		}

		$percent = intval($percent);

		if($percent < -1 || $percent > 100){
			return $player->showModal(new self($this->essence, TF::RED . "Percent must be within 1-100.\n\n"));
		}

		$isRaw = (bool) $response[4];

		$essence = clone $this->essence;
		$essence->setup($rarity, $cost, $percent, $isRaw)->init();

		if(!$player->getInventory()->canAddItem($essence)){
			return $player->sendMessage(TF::RI . "Item can not be added to inventory\n\n");
		}

		$player->getInventory()->addItem($essence);
		$player->setCurrentWindow(new EssenceSelectionInventory($this->essence->getCount()));
	}
}
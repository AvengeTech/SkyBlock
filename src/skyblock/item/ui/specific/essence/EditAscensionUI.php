<?php

namespace skyblock\item\ui\specific\essence;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\elements\customForm\Toggle;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\item\EssenceOfAscension;
use skyblock\item\inventory\specific\essence\EssenceSelectionInventory;

class EditAscensionUI extends CustomForm{

	public function __construct(
		private EssenceOfAscension $essence, 
		string $label = ""
	){
		parent::__construct("Edit Ascension");

		$label .= "Edit the essence data below.";

		$this->addElement(new Label($label));
		$this->addElement(new Input("Rarity", "1-5", "1"));
		$this->addElement(new Toggle("isRaw", true));
	}

	public function handle($response, AtPlayer $player){
		if(empty(trim($response[1]))){
			return $player->showModal(new self($this->essence, TF::RED . "Input can not be empty.\n\n"));
		}

		$rarity = $response[1];

		if(!is_numeric($rarity)){
			return $player->showModal(new self($this->essence, TF::RED . "Rarity must be numeric.\n\n"));
		}

		$rarity = intval($rarity);

		if($rarity < 1 || $rarity > 5){
			return $player->showModal(new self($this->essence, TF::RED . "Rarity must be within 1-5.\n\n"));
		}

		$isRaw = (bool) $response[2];

		$essence = clone $this->essence;
		$essence->setup($rarity, $isRaw)->init();

		if(!$player->getInventory()->canAddItem($essence)){
			return $player->sendMessage(TF::RI . "Item can not be added to inventory\n\n");
		}

		$player->getInventory()->addItem($essence);
		$player->setCurrentWindow(new EssenceSelectionInventory($this->essence->getCount()));
	}
}
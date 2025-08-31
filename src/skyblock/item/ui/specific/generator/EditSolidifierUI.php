<?php

namespace skyblock\item\ui\specific\generator;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\generators\item\Solidifier;
use skyblock\item\inventory\SpecialItemsInventory;

class EditSolidifierUI extends CustomForm{

	public function __construct(
		private Solidifier $item,
		string $label = ""
	){
		parent::__construct("Edit Solidifer");

		$label .= "Edit Solidifier data below.";

		$this->addElement(new Label($label));
		$this->addElement(new Input("Level", "1", "1"));
		$this->addElement(new Input("Runs", "500", "500"));
	}

	public function handle($response, AtPlayer $player){
		$level = $response[1];

		if($level < 1 || $level > 5){
			return $player->showModal(new self($this->item, TF::RED . "Solidifier level must be between " . TF::YELLOW . "1" . TF::RED . " to " . TF::YELLOW . "5" . TF::GRAY . "."));
		}

		$runs = $response[2];

		if($runs < 1 || $runs > 100000){
			return $player->showModal(new self($this->item, TF::RED . "Solidifier runs must be between " . TF::YELLOW . "1" . TF::RED . " to " . TF::YELLOW . "100000" . TF::GRAY . "."));
		}

		$this->item->setup($level, $runs)->init();

		$player->getInventory()->addItem($this->item);
		$player->setCurrentWindow(new SpecialItemsInventory());
	}
}
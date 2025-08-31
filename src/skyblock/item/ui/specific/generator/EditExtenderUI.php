<?php

namespace skyblock\item\ui\specific\generator;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\generators\item\Extender;
use skyblock\item\inventory\SpecialItemsInventory;

class EditExtenderUI extends CustomForm{

	public function __construct(
		private Extender $item,
		string $label = ""
	){
		parent::__construct("Edit Extender");

		$label .= "Edit Extender data below.";

		$this->addElement(new Label($label));
		$this->addElement(new Input("Level", "1", "1"));
	}

	public function handle($response, AtPlayer $player){
		$level = $response[1];

		if($level < 1 || $level > 2){
			return $player->showModal(new self($this->item, TF::RED . "Extender level must be between " . TF::YELLOW . "1" . TF::RED . " to " . TF::YELLOW . "2" . TF::GRAY . "."));
		}

		$this->item->setup($level)->init();

		$player->getInventory()->addItem($this->item);
		$player->setCurrentWindow(new SpecialItemsInventory());
	}
}
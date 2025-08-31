<?php

namespace skyblock\item\ui\specific\enchantments;

use core\AtPlayer;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\elements\customForm\Toggle;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\enchantments\item\EnchantmentBook;
use skyblock\item\inventory\specific\enchantments\BookSelectionInventory;

class EditBookUI extends CustomForm{

	public function __construct(
		private EnchantmentBook $item,
		string $label = ""
	){
		parent::__construct("Edit Book");

		$label .= "Edit the book data below.";

		$this->addElement(new Label($label));
		$this->addElement(new Input("Apply Cost", "-1", "-1"));
		$this->addElement(new Input("Apply Chance", "-1", "-1"));
		$this->addElement(new Toggle("Rerolled", false));
	}

	public function handle($response, AtPlayer $player){
		for($i = 0; $i < 2; $i++){
			if(empty(trim($response[$i]))){
				return $player->showModal(new self($this->item, TF::RED . "Input can not be empty.\n\n"));
			}
		}

		$cost = $response[1];

		if($cost < -1 || $cost > 1000){
			return $player->showModal(new self($this->item, TF::RED . "Book cost must be between " . TF::YELLOW . "-1" . TF::RED . " to " . TF::YELLOW . "1000" . TF::GRAY . ".\n\n"));
		}

		$chance = $response[2];

		if($chance < -1 || $chance > 100){
			return $player->showModal(new self($this->item, TF::RED . "Book chance must be between " . TF::YELLOW . "-1" . TF::RED . " to " . TF::YELLOW . "100" . TF::GRAY . ".\n\n"));
		}

		$rerolled = $response[3];

		$this->item->setup($this->item->getEnchant(), $cost, $chance, $this->item->getEnchantmentCategory(), $rerolled);

		$player->getInventory()->addItem($this->item);
		$player->setCurrentWindow(new BookSelectionInventory($this->item->getRarity()));
	}
}
<?php

namespace skyblock\item\ui\specific\enchantments;

use core\AtPlayer;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat as TF;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\item\inventory\SpecialItemsInventory;
use skyblock\item\inventory\specific\enchantments\BookSelectionInventory;

class BookRarityUI extends SimpleForm{

	public function __construct(){
		parent::__construct("Book Rarity", "Select a book rarity");

		$this->addButton(new Button(TF::DARK_RED . "Back"));

		for($i = 1; $i <= 5; $i++){
			$this->addButton(new Button(ED::rarityColor($i) . TF::BOLD . ED::rarityName($i)));
		}
	}

	public function handle($response, AtPlayer $player){
		if($response === 0){
			$player->setCurrentWindow(new SpecialItemsInventory);
			return;
		}

		$player->setCurrentWindow(new BookSelectionInventory($response));
	}
}
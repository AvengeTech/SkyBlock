<?php

namespace skyblock\enchantments\uis\conjuror;

use core\AtPlayer;
use core\ui\elements\customForm\Dropdown;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\TextFormat as TF;
use skyblock\enchantments\item\EnchantmentBook;
use skyblock\enchantments\uis\conjuror\confirm\ConfirmCombineUI;
use skyblock\item\EssenceOfKnowledge;
use skyblock\SkyBlockPlayer;

class CombineBooksUI extends CustomForm{

	/** @var EnchantmentBook[] $books */
	private array $books = [];
	/** @var EssenceOfKnowledge[] $essences */
	private array $essences = [];

	public function __construct(AtPlayer $player){
		parent::__construct("Combine Books");

		$this->addElement(new Label("Which essence would you like to use?"));

		$dropdown = new Dropdown("Essence Selection");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item instanceof EssenceOfKnowledge && !$item->isRaw()){
				$this->essences[$key] = $item;
				$msgStart = ($item->getCount() > 1 ? TF::WHITE . $item->getCount() . "x " : "");
				$dropdown->addOption($msgStart . $item->getName() . TF::RESET . TF::YELLOW . " (XP Levels: " . $item->getCost() . ')');
				$key++;
			}
		}
		$this->addElement($dropdown);

		$this->addElement(new Label("Which book would you like to combine?"));

		$dropdown = new Dropdown("Book Selection");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item instanceof EnchantmentBook && $item->getEnchant()->getStoredLevel() !== $item->getEnchant()->getMaxLevel()){
				$this->books[$key] = $item;
				$dropdown->addOption($item->getEnchant()->getLore($item->getEnchant()->getStoredLevel()));
				$key++;
			}
		}
		$this->addElement($dropdown);
	}

	public function handle($response, AtPlayer $player){
		/** @var SkyBlockPlayer $player */
		if(empty($this->essences) || empty($this->books)) return;
		
		$essence = $this->essences[$response[1]];
		$essenceSlot = $player->getInventory()->first($essence, true);
		if($essenceSlot === -1){
			$player->sendMessage(TF::RI . "This essence is no longer in your inventory!");
			return;
		}
		
		$book = $this->books[$response[3]];
		$firstBookSlot = $player->getInventory()->first($book, true);
		if($firstBookSlot === -1){
			$player->sendMessage(TF::RI . "This book is no longer in your inventory!");
			return;
		}

		$secondBookSlot = null;

		foreach($player->getInventory()->getContents(true) as $index => $item){
			if($index === $firstBookSlot) continue;

			if(
				$item instanceof EnchantmentBook && 
				$item->getEnchant()->getRuntimeId() === $book->getEnchant()->getRuntimeId() &&
				$item->getEnchant()->getStoredLevel() === $book->getEnchant()->getStoredLevel()
			){
				$secondBookSlot = $index;
				break;
			}
		}

		if(is_null($secondBookSlot)){
			$player->sendMessage(TF::RI . "You must have two of the same books to combine!");
			return;
		}
	
		if($essence->getCost() > $player->getGameSession()->getEssence()->getEssence()){
			$player->sendMessage(TF::RI . "You don't have enough essence to use this essence of success!");
			return;
		}

		$player->showModal(new ConfirmCombineUI($essence, $book, $secondBookSlot));
	}
}
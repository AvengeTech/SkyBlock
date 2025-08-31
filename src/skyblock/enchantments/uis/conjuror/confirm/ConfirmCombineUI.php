<?php

namespace skyblock\enchantments\uis\conjuror\confirm;

use core\AtPlayer;
use core\ui\windows\ModalWindow;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use skyblock\enchantments\item\EnchantmentBook;
use skyblock\enchantments\uis\conjuror\CombineBooksUI;
use skyblock\item\EssenceOfKnowledge;
use skyblock\SkyBlockPlayer;

class ConfirmCombineUI extends ModalWindow{

	public function __construct(
		private EssenceOfKnowledge $essence,
		private EnchantmentBook $book,
		private int $secondBookSlot
	){
		$content = "Are you sure you want to combine two " . $book->getEnchant()->getLore($book->getEnchant()->getStoredLevel()) . TF::WHITE . " to get " . $book->getEnchant()->getLore($book->getEnchant()->getStoredLevel() + 1) . TF::WHITE . "?" . "\n\n";
		$content .= "Combining these books will cost " . TF::DARK_AQUA . $essence->getCost() . " Essence";

		parent::__construct(
			"Confirm Combine", 
			$content,
			"Combine Books",
			"Go Back"
		);
	}

	public function handle($response, AtPlayer $player){
		/** @var SkyBlockPlayer $player */
		if($response){
			$essenceSlot = $player->getInventory()->first($this->essence, true);
			if($essenceSlot == -1){
				$player->sendMessage(TF::RN . "The essence you are trying to use is no longer in your inventory!");
				return;
			}

			if($player->getGameSession()->getEssence()->getEssence() < $this->essence->getCost()){
				$player->sendMessage(TF::RN . "You do not have enough Essence to use this!");
				return;
			}


			$firstBookSlot = $player->getInventory()->first($this->book, true);
			if($firstBookSlot === -1){
				$player->sendMessage(TF::RI . "This book is no longer in your inventory!");
				return;
			}

			/** @var EnchantmentBook $secondBook */
			$secondBook = $player->getInventory()->getItem($this->secondBookSlot);

			// This should not really happen unless a Tier 3 or Shanelly is messing with you inventory!
			if(!$secondBook->equals($this->book, false, false) && $this->book->getEnchant()->getStoredLevel() !== $secondBook->getEnchant()->getStoredLevel()){
				$player->sendMessage(TF::RI . "The second book does not match, it has been moved to a different slot or removed from your inventory!");
				return;
			}

			$enchantment = $this->book->getEnchant()->setStoredLevel($this->book->getEnchant()->getStoredLevel() + 1);

			$newBook = ItemRegistry::REDEEMED_BOOK()->setup(
				$enchantment, 
				max($this->book->getApplyCost(), $secondBook->getApplyCost()),
				max($this->book->getApplyChance(), $secondBook->getApplyChance()),
				$this->book->getEnchantmentCategory(),
				($this->book->hasRerolled() || $secondBook->hasRerolled())
			);

			$this->essence->pop();

			$player->getGameSession()->getEssence()->subEssence($this->essence->getCost());
			$player->getInventory()->setItem($essenceSlot, $this->essence);
			$player->getInventory()->clear($firstBookSlot);
			$player->getInventory()->clear($this->secondBookSlot);
			$player->getInventory()->addItem($newBook);

			$player->sendMessage(TF::GI . "You have combined two books and received " . $newBook->getCustomName() . TF::GRAY . "!");
		}else{
			$player->showModal(new CombineBooksUI($player));
		}
	}
}
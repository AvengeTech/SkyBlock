<?php

namespace skyblock\item\inventory;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\player\Player;
use skyblock\item\inventory\specific\enchantments\BookSelectionInventory;
use skyblock\item\inventory\specific\essence\EssenceSelectionInventory;
use skyblock\item\inventory\specific\pet\EggSelectionInventory;

class SpecialItemsInventoryHandler{

	public static function handle(Inventory $inventory, Player $player, InventoryTransaction $transaction) : bool{
		if($inventory instanceof SpecialItemsInventory){
			foreach($transaction->getActions() as $action){
				$inventory->handle($player, $action->getTargetItem());
			}

			return true;
		}elseif($inventory instanceof EggSelectionInventory){
			foreach($transaction->getActions() as $action){
				$inventory->handle($player, $action->getTargetItem());
			}

			return true;
		}elseif($inventory instanceof BookSelectionInventory){
			foreach($transaction->getActions() as $action){
				$inventory->handle($player, $action->getTargetItem());
			}

			return true;
		}elseif($inventory instanceof EssenceSelectionInventory){
			foreach($transaction->getActions() as $action){
				$inventory->handle($player, $action->getTargetItem());
			}

			return true;
		}

		return false;
	}
}
<?php

declare(strict_types=1);

namespace skyblock\inventory;

use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\item\Item;

use skyblock\enchantments\type\ToggledArmorEnchantment;

class EnchantmentInventoryListener implements InventoryListener{

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem) : void{
		if(!$inventory instanceof ArmorInventory) return;

		$newItem = $inventory->getItem($slot);
		$player = array_values($inventory->getViewers())[0];

		ToggledArmorEnchantment::onToggle($player, $oldItem, $newItem);
	}

	public function onContentChange(Inventory $inventory, array $oldContents) : void{
		if(!$inventory instanceof ArmorInventory) return;

		$newContents = $inventory->getContents(true);
		$player = array_values($inventory->getViewers())[0];

		foreach($newContents as $i => $item){
			if($item === $oldContents[$i]) continue;

			ToggledArmorEnchantment::onToggle($player, $oldContents[$i], $item);
		}
	}
}
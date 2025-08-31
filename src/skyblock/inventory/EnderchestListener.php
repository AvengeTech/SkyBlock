<?php

namespace skyblock\inventory;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\item\Item;
use pocketmine\Server;

class EnderchestListener implements InventoryListener {

	public function __construct(protected EnderchestInventory $enderInv) {
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem): void {
		$this->onContentChange($inventory, []);
	}

	public function onContentChange(Inventory $inventory, array $oldContents): void {
		if (Server::getInstance()->getTick() !== $this->enderInv->updateTick) $this->enderInv->push();
	}
}

<?php namespace skyblock\trash;

use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\trash\tiles\{
	TrashInventory
};
use skyblock\trash\tasks\{
	TrashClearTask
};
use skyblock\trash\commands\OpenTrash;

use core\utils\TextFormat;

class Trash{

	const TRASH_EMPTY = 30;

	public $plugin;

	public $inventories = [];
	public $restart;

	public function __construct(SkyBlock $plugin){
		$this->plugin = $plugin;

		for($i = 1; $i <= 3; $i++)
			$this->inventories[$i] = new TrashInventory($i);

		$this->restart = time() + self::TRASH_EMPTY;

		$plugin->getServer()->getCommandMap()->register("opentrash", new OpenTrash($plugin, "opentrash", "Opens the trash can to dispose of items."));
	}

	public function close() : void{

	}

	public function tick() : void{
		if(time() >= $this->restart){
			$this->restart = self::TRASH_EMPTY + time();
			$this->plugin->getScheduler()->scheduleDelayedTask(new TrashClearTask($this->plugin), 1);
		}

		$restart = $this->restart - time();

		$item = VanillaBlocks::STONE()->asItem()->setCount($this->restart - time());
		$item->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Clearing in " . TextFormat::RED . $restart . TextFormat::GRAY . " seconds...");
		foreach($this->getInventories() as $inventory) $inventory->setItem(53, $item);
	}

	public function getInventories() : array{
		return $this->inventories;
	}

	public function getInventory(int $id = 1) : ?TrashInventory{
		return $this->inventories[$id] ?? null;
	}

	public function open(Player $player, int $id = 1) : bool{
		$inventory = $this->getInventory($id);
		if($inventory == null){
			$player->sendMessage(TextFormat::RI . "Invalid trash ID! (1-3)");
			return false;
		}

		$player->getNetworkSession()->getInvManager()->getContainerOpenCallbacks()->add(function(int $id, Inventory $inventory) : array{
			return []; //trollface
		});
		$player->setCurrentWindow($inventory);
		return true;
	}

}
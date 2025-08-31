<?php namespace skyblock\shop\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player
};
use skyblock\islands\permission\Permissions;

use core\utils\TextFormat;
use core\network\Links;

class SellInventoryCommand extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["sellinv", "si", "sellall", "sa"]);
	}

	/** @param Player $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if($sender->getRank() == "default"){
			$sender->sendMessage(TextFormat::RI . "You must have a premium rank to sell all items in your inventory! Purchase one at " . TextFormat::YELLOW . Links::SHOP);
			return;
		}

		$isession = $sender->getGameSession()->getIslands();
		$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();
		if(is_null($island)){
			$sender->sendMessage(TextFormat::RI . "You can only use the island shop for the last island you visited!");
			return;
		}
		$perm = $island->getPermissions()->getPermissionsBy($sender);
		if(
			is_null($perm) || 
			!$perm->getPermission(Permissions::USE_SHOP)
		){
			$sender->sendMessage(TextFormat::RI . "You do not have permission to use this island's shop!");
			return;
		}

		$shop = SkyBlock::getInstance()->getShops();
		$array = $shop->sellInventory($sender);
		if($array["count"] <= 0){
			$sender->sendMessage(TextFormat::RI . "No items in your inventory were able to be sold.");
			return;
		}

		$sender->sendMessage(TextFormat::GI . "Sold " . $array["count"] . " items in inventory for " . TextFormat::AQUA . $array["price"] . " Techits");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
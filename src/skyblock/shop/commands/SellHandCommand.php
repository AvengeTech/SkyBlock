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

class SellHandCommand extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["sh"]);
	}

	/** @param Player $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
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
		if(($price = $shop->sellHand($sender)) <= 0){
			$sender->sendMessage(TextFormat::RI . "Item in hand isn't available for sale at your island level.");
			return;
		}

		$sender->sendMessage(TextFormat::GI . "Sold item in hand for " . TextFormat::AQUA . $price . " Techits");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
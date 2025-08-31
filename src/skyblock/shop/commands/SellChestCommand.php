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

class SellChestCommand extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["sc"]);
	}

	/** @param Player $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if($sender->getRank() == "default"){
			$sender->sendMessage(TextFormat::RI . "You must have a premium rank to sell all items in a chest! Purchase one at " . TextFormat::YELLOW . Links::SHOP);
			return;
		}

		$isession = $sender->getGameSession()->getIslands();
		if(!$isession->atIsland()){
			$sender->sendMessage(TextFormat::RI . "You can only use the island shop at an island!");
			return;
		}
		$island = $isession->getIslandAt();
		$perm = $island->getPermissions()->getPermissionsBy($sender);

		if(
			is_null($perm) || 
			!$perm->getPermission(Permissions::USE_SHOP) || 
			!$perm->getPermission(Permissions::USE_SELL_CHEST)
		){
			$sender->sendMessage(TextFormat::RI . "You do not have permission to sell chests at this island!");
			return;
		}

		$shop = SkyBlock::getInstance()->getShops();

		if($shop->chestMode($sender)){
			$sender->sendMessage(TextFormat::GI . "Sell Chest mode enabled. Tap a chest to sell all the items in it!");
		}else{
			$sender->sendMessage(TextFormat::RI . "Sell Chest mode disabled.");
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
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
use skyblock\shop\ui\ShopUi;

use core\utils\TextFormat;
use skyblock\shop\ui\CategoryUi;

class ShopCommand extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/** @param Player $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$isession = $sender->getGameSession()->getIslands();
		$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();
		if($island === null){
			$sender->sendMessage(TextFormat::RI . "You can only use the island shop for the last island you visited!");
			return;
		}
		$perm = $island->getPermissions()->getPermissionsBy($sender);
		if($perm === null || !$perm->getPermission(Permissions::USE_SHOP)){
			$sender->sendMessage(TextFormat::RI . "You do not have permission to use this island's shop!");
			return;
		}

		if(isset($args[0])){
			$shopLevel = intval($args[0]);

			if($shopLevel <= 0) $shopLevel = 1;

			if($shopLevel > $island->getSizeLevel()){
				$sender->sendMessage(TextFormat::RI . "You can not access that level shop!");
				return;
			}
			
			$sender->showModal(new CategoryUi($shopLevel, false));
		}else{
			$sender->showModal(new ShopUi($sender));
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
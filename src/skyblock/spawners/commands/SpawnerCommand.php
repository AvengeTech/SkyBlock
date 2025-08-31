<?php namespace skyblock\spawners\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;

use core\utils\TextFormat;

class SpawnerCommand extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/** @param SkyBlockPlayer $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$isession = $sender->getGameSession()->getIslands();
		if(!$isession->atIsland()){
			$sender->sendMessage(TextFormat::RI . "You must be at an island to use this command!");
			return;
		}
		$island = $isession->getIslandAt();
		$perm = $island->getPermissions()->getPermissionsBy($sender) ?? $island->getPermissions()->getDefaultVisitorPermissions();

		if(!$perm->getPermission(Permissions::EDIT_SPAWNERS)){
			$sender->sendMessage(TextFormat::RI . "You don't have permission to edit spawners on this island");
			return;
		}

		$session = $sender->getGameSession()->getSpawners();
		$session->toggle();
		if($session->isToggled()){
			$sender->sendMessage(TextFormat::YN . "Tap on a spawner you'd like to see information about.");
		}else{
			$sender->sendMessage(TextFormat::YN . "Spawner selection mode deactivated.");
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
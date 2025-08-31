<?php namespace skyblock\trash\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;

use skyblock\SkyBlock;

use core\utils\TextFormat;

class OpenTrash extends CoreCommand{

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setInGameOnly();
		$this->setAliases(["ot", "t", "trash"]);
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$id = array_shift($args);
		if($id === null || !is_numeric($id)) $id = 1;
		if($id < 1 || $id > 3){
			$sender->sendMessage(TextFormat::RI . "Invalid trash ID! (1-3)");
			return;
		}

		$this->plugin->getTrash()->open($sender, $id);
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
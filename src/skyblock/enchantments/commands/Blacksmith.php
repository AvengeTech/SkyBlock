<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\uis\blacksmith\BlacksmithUi;

class Blacksmith extends CoreCommand{

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["bs"]);
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new BlacksmithUi());
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
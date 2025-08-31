<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;
use skyblock\enchantments\uis\conjuror\ViewRefineryUI;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class Refinery extends CoreCommand{

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

		/** @param SkyBlockPlayer $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new ViewRefineryUI($sender));
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
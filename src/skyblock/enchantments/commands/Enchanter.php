<?php

namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\uis\enchanter\SelectItemUi;

class Enchanter extends CoreCommand {

	public function __construct(public \skyblock\SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["ench"]);
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new SelectItemUi($sender));
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
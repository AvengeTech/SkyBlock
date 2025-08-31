<?php

namespace skyblock\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\network\Links;
use core\rank\Rank;
use core\rank\Structure;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use skyblock\commands\inventory\EnderchestInventory;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;
use core\rank\Structure as RS;

class EnderChest extends CoreCommand {
	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(RS::RANK_HIERARCHY['ghast']);
		$this->setAliases(['ec', 'echest']);
		$this->setInGameOnly();
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->getEnderChest()->doOpen();
		$sender->sendMessage(TextFormat::GN . "Opening your Ender Chest...");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}

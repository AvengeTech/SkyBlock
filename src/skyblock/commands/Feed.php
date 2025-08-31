<?php

namespace skyblock\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use core\network\Links;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use core\rank\Structure as RS;

class Feed extends CoreCommand {

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setHierarchy(RS::RANK_HIERARCHY["endermite"]);
		$this->setInGameOnly();
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->getHungerManager()->setFood(20);
		$sender->getHungerManager()->setExhaustion(0);
		$sender->getHungerManager()->setSaturation(20);
		$sender->sendMessage(TextFormat::GN . "Your hunger bar has been filled!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}
}
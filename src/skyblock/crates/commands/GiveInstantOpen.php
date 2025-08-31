<?php namespace skyblock\crates\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\Core;
use core\rank\Rank;
use core\utils\TextFormat;

class GiveInstantOpen extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setAliases(["gio"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args)
	{
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RN . "Usage: /giveinstantopen <player> <minutes>");
			return;
		}

		$name = array_shift($args);
		$minutes = (int) array_shift($args);

		/** @var SkyBlockPlayer $player */
		$player = $this->plugin->getServer()->getPlayerExact($name);

		if(!$player instanceof Player){
			$sender->sendMessage(TextFormat::RN . "Player not online!");
			return;
		}

		$session = $player->getGameSession()->getCrates();
		$session->setInstantOpen($minutes);

		$player->sendMessage(TextFormat::GI . "You have received " . TextFormat::YELLOW . $minutes . " minutes " . TextFormat::GRAY . "of instant crate opening!");
		$sender->sendMessage(TextFormat::GN . "Successfully gave " . $player->getName() . " " . $minutes . " minutes of instant crate opening time!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
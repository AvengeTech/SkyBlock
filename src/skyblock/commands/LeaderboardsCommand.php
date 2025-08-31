<?php

namespace skyblock\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;
use pocketmine\world\Position;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use core\Core;
use core\network\protocol\PlayerLoadActionPacket;
use core\staff\anticheat\session\SessionManager;

class LeaderboardsCommand extends CoreCommand {

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setAliases(["lb"]);
		$this->setInGameOnly();
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(($ts = Core::thisServer())->isSubServer()){
			($pk = new PlayerLoadActionPacket([
				"player" => $sender->getName(),
				"server" => $ts->getParentServer()->getIdentifier(),
				"action" => "leaderboards",
			]))->queue();
			$sender->gotoSpawn(TextFormat::GN . "Teleported to leaderboards!");
			return;
		}

		$isession = $sender->getGameSession()->getIslands();
		if($isession->atIsland()){
			$isession->setIslandAt(null);
		}

		$ksession = $sender->getGameSession()->getKoth();
		if($ksession->inGame()){
			$ksession->setGame();
		}

		$lsession = $sender->getGameSession()->getLms();
		if($lsession->inGame()){
			$lsession->setGame();
		}

		$sender->teleport(new Position(-14695.5, 123, 13666.5, $this->plugin->getServer()->getWorldManager()->getWorldByName("scifi1")), 45, 0);
		$sender->setAllowFlight(true);
		$sender->sendMessage(TextFormat::GN . "Teleported to leaderboards!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
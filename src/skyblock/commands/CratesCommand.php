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
use skyblock\crates\ui\RemoteCratesUi;

class CratesCommand extends CoreCommand {

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(($ts = Core::thisServer())->isSubServer()){
			if ($sender->getSession()->getRank()->hasSub()) {
				$sender->sendMessage(TextFormat::GN . "Opening crates UI...");
				$sender->showModal(new RemoteCratesUi($sender));
				return;
			}
			($pk = new PlayerLoadActionPacket([
				"player" => $sender->getName(),
				"server" => $ts->getParentServer()->getIdentifier(),
				"action" => "crates",
			]))->queue();
			$sender->gotoSpawn(TextFormat::GN . "Teleported to crates!");
			return;
		}

		$isession = $sender->getGameSession()->getIslands();
		if($isession->atIsland()){
			$isession->setIslandAt(null);
		}

		$ps = $sender->getGameSession()->getParkour();
		if($ps->hasCourseAttempt()){
			$ps->getCourseAttempt()->removeScoreboard();
			$ps->setCourseAttempt();
		}

		$ksession = $sender->getGameSession()->getKoth();
		if($ksession->inGame()){
			$ksession->setGame();
		}

		$lsession = $sender->getGameSession()->getLms();
		if($lsession->inGame()){
			$lsession->setGame();
		}

		$sender->teleport(new Position(-14695.5, 123, 13500.5, $this->plugin->getServer()->getWorldManager()->getWorldByName("scifi1")), 135, 0);
		$sender->setAllowFlight(true);
		$sender->sendMessage(TextFormat::GN . "Teleported to crates!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
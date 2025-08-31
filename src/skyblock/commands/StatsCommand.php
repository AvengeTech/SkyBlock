<?php

namespace skyblock\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use skyblock\{
	SkyBlock,
	SkyBlockSession
};
use skyblock\SkyBlockPlayer;
use skyblock\utils\stats\StatsUi;
use core\Core;
use core\user\User;

class StatsCommand extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(count($args) === 0){
			$sender->showModal(new StatsUi($sender->getGameSession()));
			return;
		}
		$name = array_shift($args);
		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender) : void{
			if(!$sender->isConnected()) return;
			if(!$user->valid()){
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($sender) : void{
				if(!$sender->isConnected()) return;
				$sender->showModal(new StatsUi($session));
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}
}
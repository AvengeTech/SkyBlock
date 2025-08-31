<?php
namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;

use core\Core;
use core\user\User;
use core\utils\TextFormat;

class MyEssence extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setAliases(["essence"]);
		$this->setInGameOnly();
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(count($args) == 0 || !$sender->isStaff()){
			$sender->sendMessage(TextFormat::YN . "You have " . TextFormat::DARK_AQUA . number_format($sender->getGameSession()->getEssence()->getEssence()) . " Essence");
			return;
		}

		if(count($args) == 0){
			$sender->sendMessage(TextFormat::RI . "Please enter a username!");
			return;
		}

		$name = array_shift($args);
		$player = Server::getInstance()->getPlayerByPrefix($name);
		if($player instanceof Player){
			$name = $player->getName();
		}

		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender) : void{
			if(!$user->valid()){
				if($sender->isConnected()) $sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($sender, $user) : void{
				if($sender->isConnected()) $sender->sendMessage(TextFormat::YN . "Player " . TextFormat::YELLOW . $user->getGamertag() . TextFormat::GRAY . " has " . TextFormat::DARK_AQUA . number_format($session->getEssence()->getEssence()) . " essence");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
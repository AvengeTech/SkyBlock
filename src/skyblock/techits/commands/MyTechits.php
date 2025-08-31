<?php namespace skyblock\techits\commands;

use core\command\type\CoreCommand;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player,
	SkyBlockSession
};

use core\Core;
use core\user\User;
use core\utils\TextFormat;

class MyTechits extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setAliases(["mymoney", "techits", "money"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args)
	{
		if($sender instanceof Player){
			if(count($args) == 0 || !$sender->isStaff()){
				$sender->sendMessage(TextFormat::YN . "You have " . TextFormat::AQUA . number_format($sender->getTechits()) . " Techits");
				return;
			}
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
				if (!$sender instanceof Player || $sender->isOnline()) $sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($sender, $user) : void{
				if (!$sender instanceof Player || $sender->isOnline()) $sender->sendMessage(TextFormat::YN . "Player " . TextFormat::YELLOW . $user->getGamertag() . TextFormat::GRAY . " has " . TextFormat::AQUA . number_format($session->getTechits()->getTechits()) . " techits");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
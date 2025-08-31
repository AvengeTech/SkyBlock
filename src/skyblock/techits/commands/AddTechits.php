<?php namespace skyblock\techits\commands;

use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player,
	SkyBlockSession
};

use core\Core;
use core\rank\Rank;
use core\user\User;
use core\utils\TextFormat;

class AddTechits extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) : void{
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RI . "Usage: /addtechits <player> <amount>");
			return;
		}

		$name = array_shift($args);
		$amount = (int) array_shift($args);

		$player = $this->plugin->getServer()->getPlayerByPrefix($name);
		if($player instanceof Player){
			$name = $player->getName();
		}

		if($amount <= 0 || $amount > 100000000){
			$sender->sendMessage(TextFormat::RI . "Amount must be between 0 and 100,000,000!");
			return;
		}

		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender, $amount) : void{
			if(!$user->valid()){
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($sender, $user, $amount) : void{
				$session->getTechits()->addTechits($amount);
				if(!$user->validPlayer()){
					$session->getTechits()->saveAsync();
				}else{
					$user->getPlayer()->sendMessage(TextFormat::GI . "You have earned " . TextFormat::AQUA . $amount . " Techits" . TextFormat::GRAY . "!");
				}
				$sender->sendMessage(TextFormat::GI . "Successfully gave " . TextFormat::YELLOW . $user->getGamertag() . TextFormat::AQUA . " " . $amount . " Techits" . TextFormat::GRAY."!");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
<?php
namespace skyblock\enchantments\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use core\AtPlayer as Player;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;

use core\Core;
use core\user\User;
use core\utils\TextFormat;

class AddEssence extends CoreCommand {

	public function __construct(public \skyblock\SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) : void{
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RI . "Usage: /addessence <player> <amount>");
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
				$session->getEssence()->addEssence($amount);
				if(!$user->validPlayer()){
					$session->getEssence()->saveAsync();
				}else{
					$user->getPlayer()->sendMessage(TextFormat::GI . "You have earned " . TextFormat::DARK_AQUA . $amount . " Essence" . TextFormat::GRAY . "!");
				}
				$sender->sendMessage(TextFormat::GI . "Successfully gave " . TextFormat::YELLOW . $user->getGamertag() . TextFormat::DARK_AQUA . " " . $amount . " Essence" . TextFormat::GRAY."!");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
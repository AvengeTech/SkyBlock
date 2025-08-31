<?php namespace skyblock\crates\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;

use core\Core;
use core\rank\Rank;
use core\user\User;
use core\utils\TextFormat;

class AddKeys extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args)
	{
		if(count($args) != 3){
			$sender->sendMessage(TextFormat::RN . "Usage: /addkeys <player> <type> <amount>");
			return;
		}

		$name = strtolower(array_shift($args));
		$type = strtolower(array_shift($args));
		$amount = (int) array_shift($args);

		$player = $this->plugin->getServer()->getPlayerExact($name);
		if($player instanceof Player){
			$name = $player->getName();
		}

		if($amount <= 0 || $amount >= 100000000){
			$sender->sendMessage(TextFormat::RN . "Amount must be a number between 1 and 100,000,000");
			return;
		}

		if(!in_array($type, ["iron", "gold", "diamond", "emerald", "divine", "vote"])){
			$sender->sendMessage(TextFormat::RN . "Invalid key type!");
			return;
		}

		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender, $player, $type, $amount) : void{
			if($sender instanceof Player && !$sender->isConnected()) return;
			if(!$user->valid()){
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($sender, $player, $type, $amount) : void{
				if($sender instanceof Player && !$sender->isConnected()) return;
				$session->getCrates()->addKeys($type, $amount);
				if($player instanceof Player && $player->isConnected()){
					$player->sendMessage(TextFormat::GI . "You have received " . TextFormat::YELLOW . "x" . $amount . " " . $type . " keys!");
				}else{
					$session->getCrates()->saveAsync();
				}
				$sender->sendMessage(TextFormat::GN . "Successfully gave " . $amount . " " . $type . " keys to " . $session->getUser()->getGamertag() . "!");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
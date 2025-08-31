<?php namespace skyblock\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;

use core\Core;
use core\user\User;

class PlaytimeCommand extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setAliases(["pt"]);
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if(!$sender instanceof Player && count($args) == 0){
			$sender->sendMessage(TextFormat::RI . "Usage: /playtime <player>");
			return;
		}

		if($sender instanceof Player && count($args) > 0){
			if(!$sender->isStaff() && SkyBlock::getInstance()->hasPlaytimeCooldown($sender)){
				$sender->sendMessage(TextFormat::RI . "You must wait another " . TextFormat::YELLOW . SkyBlock::getInstance()->getPlaytimeCooldown($sender) . " seconds" . TextFormat::GRAY . " before searching another player's playtime!");
				return;
			}
		}

		$search = function(SkyBlockSession $session, bool $other) use($sender) : void{
			if($sender instanceof Player && !$sender->isConnected()) return;
			$time = $session->getPlaytime()->getFormattedPlaytime(!$other);
			$sender->sendMessage(TextFormat::YI . ($other ? $session->getUser()->getGamertag() . "'s" : "Your") . " playtime: " . TextFormat::WHITE . $time);
		};
		$other = false;
		if(count($args) == 0){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RI . "Beans boi");
				return;
			}
			$session = $sender->getGameSession();
			$search($session, $other);
		}else{
			$other = true;
			$name = array_shift($args);
			$player = Server::getInstance()->getPlayerByPrefix($name);
			if($player instanceof Player) $name = $player->getName();
			Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender, $search) : void{
				if($sender instanceof Player && !$sender->isConnected()) return;
				if(!$user->valid()){
					$sender->sendMessage(TextFormat::RI . "Player never seen!");
					return;
				}
				SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($search) : void{
					$search($session, true);
				});
			});
		}
		if($sender instanceof Player) SkyBlock::getInstance()->setPlaytimeCooldown($sender);
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
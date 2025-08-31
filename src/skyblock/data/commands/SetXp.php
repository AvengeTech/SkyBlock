<?php namespace skyblock\data\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\utils\TextFormat;

class SetXp extends CoreCommand{

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RN . "Usage: /setxp <player> <amount>");
			return false;
		}

		$player = $this->plugin->getServer()->getPlayerExact(array_shift($args));
		$amount = (int) array_shift($args);

		if(!$player instanceof Player){
			$sender->sendMessage(TextFormat::RN . "Player not found!");
			return false;
		}

		if($amount < 0){
			$sender->sendMessage(TextFormat::RN . "Amount must be at least 0!");
			return false;
		}

		$player->getXpManager()->setXpAndProgressNoEvent($amount, 0.0);
		$sender->sendMessage(TextFormat::GN . "Successfully set " . $player->getName() . " " . $amount . " XP Levels");
		$player->sendMessage(TextFormat::GN . "You have " . TextFormat::YELLOW . $amount . " XP Levels");

		return true;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
<?php namespace skyblock\data\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;
use pocketmine\player\Player;

use skyblock\SkyBlock;

use core\utils\TextFormat;

class AddXp extends CoreCommand{

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RN . "Usage: /addxp <player> <amount>");
			return false;
		}

		$player = $this->plugin->getServer()->getPlayerExact(array_shift($args));
		$amount = (int) array_shift($args);

		if(!$player instanceof Player){
			$sender->sendMessage(TextFormat::RN . "Player not found!");
			return false;
		}

		if($amount <= 0){
			$sender->sendMessage(TextFormat::RN . "Amount must be at least 1!");
			return false;
		}

		$player->getXpManager()->setXpAndProgressNoEvent($player->getXpManager()->getXpLevel() + $amount, $player->getXpManager()->getXpProgress());
		$sender->sendMessage(TextFormat::GN . "Successfully gave " . $player->getName() . " " . $amount . " XP Levels");
		$player->sendMessage(TextFormat::GN . "You have received " . TextFormat::YELLOW . $amount . " XP Levels");

		return true;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
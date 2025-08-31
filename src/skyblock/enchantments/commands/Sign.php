<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;
use pocketmine\item\Durable;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\uis\SignItemUi;
use skyblock\enchantments\ItemData;

use core\utils\TextFormat;

class Sign extends CoreCommand{

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setInGameOnly();
		$this->setAliases(["signature"]);
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$item = $sender->getInventory()->getItemInHand();
		$data = new ItemData($item);
		if($data->isSigned()){
			$sender->sendMessage(TextFormat::RI . "This item is already signed!");
			return true;
		}
		if(!$item instanceof Durable){
			$sender->sendMessage(TextFormat::RI . "You can only sign tools, weapons or armor! Please hold the item you would like to sign and use this command again.");
			return true;
		}
		$sender->showModal(new SignItemUi($item));
		return false;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\uis\guide\{
	EnchantGuideUi,
	ShowGuideUi
};

use core\utils\TextFormat;
use skyblock\enchantments\EnchantmentRegistry;

class Guide extends CoreCommand{

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setAliases(["eguide", "eg", "enchantguide"]);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(empty($args)){
			$sender->showModal(new EnchantGuideUi($sender));
			return true;
		}
		$ench = EnchantmentRegistry::getEnchantmentByName(array_shift($args), $sender->isStaff());
		if($ench === null){
			$sender->sendMessage(TextFormat::RI . "Invalid enchantment name provided!");
			return false;
		}
		$sender->showModal(new ShowGuideUi($ench));
		return true;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
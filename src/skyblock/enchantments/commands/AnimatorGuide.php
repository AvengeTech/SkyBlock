<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\uis\aguide\{
	AnimatorGuideUi,
	ShowGuideUi
};

use core\utils\TextFormat;

class AnimatorGuide extends CoreCommand{

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setAliases(["aguide", "ag"]);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(empty($args)){
			$sender->showModal(new AnimatorGuideUi($sender));
			return true;
		}
		$eff = SkyBlock::getInstance()->getEnchantments()->getEffects()->getEffectByName(array_shift($args), $sender->isStaff());
		if($eff === null){
			$sender->sendMessage(TextFormat::RI . "Invalid animator name provided!");
			return false;
		}
		$sender->showModal(new ShowGuideUi($eff, false));
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
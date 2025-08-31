<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;
use pocketmine\item\enchantment\Enchantment as PME;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\ItemData;
use skyblock\enchantments\type\Enchantment;
use skyblock\enchantments\EnchantmentData as ED;

use core\utils\TextFormat;
use skyblock\enchantments\EnchantmentRegistry;

class GiveBook extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name,$description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(count($args) < 1){
			$sender->sendMessage(TextFormat::RN . "Usage: /givebook <id> <level>");
			return;
		}

		$ench = array_shift($args);
		if(is_numeric($ench)){
			$enchantment = EnchantmentRegistry::getEnchantment($ench);
		}else{
			$enchantment = EnchantmentRegistry::getEnchantmentByName($ench);
		}
		if(!$enchantment instanceof Enchantment){
			$sender->sendMessage(TextFormat::RN . "Invalid enchantment provided!");
			return;
		}


		$max = $enchantment->getMaxLevel();
		$level = (empty($args) ? $max : (int) array_shift($args));
		if($level <= 0){
			$sender->sendMessage(TextFormat::RN . "Level must be between 1-" . $max . "!");
			return;
		}
		$enchantment->setStoredLevel($level);

		$sender->getInventory()->addItem($enchantment->asBook());

		$sender->sendMessage(TextFormat::GI . "You were given a " . $enchantment->getName() . " " . $enchantment->getStoredLevel() . " book!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
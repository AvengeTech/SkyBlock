<?php namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\ItemData;
use skyblock\enchantments\EnchantmentData as ED;

use core\utils\TextFormat as TF;
use pocketmine\item\Hoe;
use skyblock\enchantments\EnchantmentRegistry;

class AddEnchantment extends CoreCommand{
	
	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name,$description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
		$this->setAliases(["addenchant", "addench", "ae"]);
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(count($args) < 1){
			$sender->sendMessage(TF::RN . "Usage: /addenchantment <id> <level>");
			return;
		}

		$item = $sender->getInventory()->getItemInHand();
		$data = new ItemData($item);

		$enches = explode(", ", implode(" ", $args));

		$aa = [];
		foreach($enches as $ee){
			$eee = explode(" ", $ee);
			if(empty($eee)) continue;

			$name = "";
			$level = -1;
			while(count($eee) > 0){
				$next = array_shift($eee);
				if(is_numeric($next)){
					$level = (int) $next;
				}else{
					$name .= $next . " ";
				}
			}
			$name = rtrim($name);

			if($level == 0) continue;

			$ench = EnchantmentRegistry::getEnchantmentByName(str_replace("_", " ", $name));
			if($ench === null) continue;

			$ench->setStoredLevel(($level == -1 ? $ench->getMaxLevel() : $level), true);
			$aa[] = $ench;
		}
		if(empty($aa)){
			$sender->sendMessage(TF::RI . "Invalid enchantments provided");
			return;
		}
		$valid = [];
		$invalid = [];
		foreach($aa as $a){
			if($item instanceof Hoe && $a->getId() === ED::EFFICIENCY){
				$data->addEnchantment($a, $a->getStoredLevel());
				$valid[] = $a->getLore($a->getStoredLevel()) . TF::GRAY;
				continue;
			}

			if(!ED::canEnchantWith($item, $a)){
				$invalid[] = $a->getLore($a->getStoredLevel());
			}else{
				$data->addEnchantment($a, $a->getStoredLevel());
				$valid[] = $a->getLore($a->getStoredLevel()) . TF::GRAY;
			}
		}

		$sender->getInventory()->setItemInHand($data->getItem());
		$sender->sendMessage(TF::GI . "Item in hand enchanted! (Added: " . implode(TF::GRAY . ", ", $valid) . ")" . (!empty($invalid) ? " (Unable to add: " . implode(TF::GRAY . ", ", $invalid) . ")" : ""));
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
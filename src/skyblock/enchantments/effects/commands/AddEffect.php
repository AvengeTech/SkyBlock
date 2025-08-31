<?php namespace skyblock\enchantments\effects\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;
use pocketmine\item\{
	Bow,
	Sword
};

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\item\NetheriteSword;
use skyblock\enchantments\effects\EffectIds;

use core\utils\TextFormat;

class AddEffect extends CoreCommand {

	public function __construct(public \skyblock\SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name,$description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if (!$this->hasPermission($sender)) {
			$sender->sendMessage("You do not have permission to use this command.");
			return false;
		}
		/** @var SkyBlockPlayer $sender */
		if($sender instanceof Player){
			if(!$sender->isTier3() && !SkyBlock::getInstance()->isTestServer()){
				$sender->sendMessage(TextFormat::RN . "You do not have permission to use this command");
				return false;
			}

			if(count($args) != 1){
				$sender->sendMessage(TextFormat::RN . "Usage: /addeffect <id:name>");
				return false;
			}

			$id = array_shift($args);
			if(is_numeric($id)){
				$effect = SkyBlock::getInstance()->getEnchantments()->getEffects()->getEffectById($id);
			}else{
				$effect = SkyBlock::getInstance()->getEnchantments()->getEffects()->getEffectByName($id);
			}
			if($effect === null){
				$sender->sendMessage(TextFormat::RN . "Invalid effect id!");
				return false;
			}

			$item = $sender->getInventory()->getItemInHand();

			if($item instanceof Bow){
				$sender->sendMessage(TextFormat::RN . "Effects can't be added to bows");
				return false;
			}
			if(($item instanceof Sword) && $effect->getType() == EffectIds::TYPE_TOOL){
				$sender->sendMessage(TextFormat::RN . "Tool effects can't be added to swords");
				return false;
			}
			if((!$item instanceof Sword) && $effect->getType() == EffectIds::TYPE_SWORD){
				$sender->sendMessage(TextFormat::RN . "Sword effects can't be added to tools");
				return false;
			}

			$data = SkyBlock::getInstance()->getEnchantments()->getItemData($item);
			$data->setEffectId($effect->getId());
			$item = $data->getItem();
			$sender->getInventory()->setItemInHand($item);

			$sender->sendMessage(TextFormat::GI . "Item in hand given effect '" . TextFormat::YELLOW . $effect->getName() . TextFormat::GRAY . "'!");
			return true;
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}
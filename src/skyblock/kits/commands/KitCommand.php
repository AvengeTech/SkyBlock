<?php namespace skyblock\kits\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player
};

use core\utils\TextFormat as TF;
use core\network\Links;
use core\utils\ItemRegistry;

class KitCommand extends CoreCommand{

	public function __construct(private SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/**
	 * @param Player $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if(empty($args)){
			$sender->sendMessage(TF::RI . "Usage: /kit <name>");
			$sender->sendMessage(SkyBlock::getInstance()->getKits()->getKitListString($sender));
			return false;
		}

		$kit = SkyBlock::getInstance()->getKits()->getKitByName($name = strtolower(array_shift($args)));

		if(is_null($kit)){
			$kit = SkyBlock::getInstance()->getKits()->getKitByShortName($name);
			if(is_null($kit)){
				$sender->sendMessage(TF::RI . "Invalid kit!");
				$sender->sendMessage(SkyBlock::getInstance()->getKits()->getKitListString($sender));
				return false;
			}
		}

		if(!$kit->hasRequiredRank($sender)){
			$sender->sendMessage(TF::RI . "You must have at least " . $kit->getRank() . " rank to claim this kit! Purchase a rank at " . TF::YELLOW . Links::SHOP);
			return false;
		}

		$session = $sender->getGameSession()->getKits();
		if(!$sender->isTier3() && $session->hasCooldown($kit->getId())){
			$sender->sendMessage(TF::RI . "You have a cooldown on this kit! Next use: " . TF::WHITE . $session->getFormattedCooldown($kit->getId()));
			return false; //todo: convert time to hours
		}

		$item = ItemRegistry::KIT_POUCH()->setup($kit->getId())->init();

		if(!$sender->getInventory()->canAddItem($item)){
			$sender->sendMessage(TF::RI . "Your inventory is full, make some space to receive items!");
			return false;
		}

		$sender->getInventory()->addItem($item);

		$session->setCooldown($kit->getId(), $kit->getCooldownTime());
		$sender->sendMessage(TF::GI . "Successfully equipped the " . $kit->getDisplayName() . TF::RESET . TF::GRAY . " kit.");
		return true;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}
}
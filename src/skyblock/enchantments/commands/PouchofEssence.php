<?php

namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\utils\ItemRegistry;
use core\utils\TextFormat;

class PouchofEssence extends CoreCommand {

	public $plugin;

	public function __construct(SkyBlock $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setAliases(['poe', "pouch", "extractessence"]);
		$this->setInGameOnly();
	}

	/** @param SkyBlockPlayer $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args) {
		$amount = (int)array_shift($args);

		if ($amount <= 0) {
			$sender->sendMessage(TextFormat::RN . "Amount must be at least 1!");
			return false;
		}

		if ($amount > $sender->getGameSession()->getEssence()->getEssence()) {
			$sender->sendMessage(TextFormat::RN . "You do not have enough Essence!");
			return false;
		}

		$item = ItemRegistry::POUCH_OF_ESSENCE();
		$item->setup($sender, $amount);
		$item->init();
		if (!$sender->getInventory()->canAddItem($item)) {
			$sender->sendMessage(TextFormat::RN . "Your inventory is full!");
			return false;
		}

		$sender->getInventory()->addItem($item);
		$sender->takeTechits($amount);

		$sender->getGameSession()->getEssence()->subEssence($amount);
		$sender->sendMessage(TextFormat::GN . "Pouch of Essence added to your inventory!");
		return true;
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}

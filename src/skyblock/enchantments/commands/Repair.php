<?php

namespace skyblock\enchantments\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;
use pocketmine\item\Durable;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\uis\blacksmith\ConfirmRepairUi;

use core\utils\TextFormat;
use core\rank\Structure as RS;

class Repair extends CoreCommand {

	public function __construct(public SkyBlock $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setAliases(["fix"]);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args) {
		if (($rh = $sender->getRankHierarchy()) < 3) {
			$sender->sendMessage(TextFormat::RN . "You must be at least " . TextFormat::WHITE . TextFormat::BOLD . "GHAST " . TextFormat::RESET . TextFormat::GRAY . "rank to use this command! You may repair your tools at the " . TextFormat::BOLD . TextFormat::DARK_GRAY . "Blacksmith " . TextFormat::RESET . TextFormat::GRAY . "(" . TextFormat::YELLOW . "/blacksmith" . TextFormat::GRAY . "), or purchase a rank! " . TextFormat::YELLOW . "store.avengetech.net");
			return false;
		}

		if (($ench = SkyBlock::getInstance()->getEnchantments())->hasCooldown($sender)) {
			$cd = $ench->getCooldownFormatted($sender);
			$sender->sendMessage(TextFormat::RI . "You must wait " . TextFormat::WHITE . $cd . TextFormat::GRAY . " to use this again!");
			return false;
		}

		$item = $sender->getInventory()->getItemInHand();
		if (!$item instanceof Durable) {
			$sender->sendMessage(TextFormat::RI . "You cannot repair this item!");
			return false;
		}
		if ($item->getDamage() == 0) {
			$sender->sendMessage(TextFormat::RI . "This item already has maximum durability!");
			return false;
		}

		$sender->showModal(new ConfirmRepairUi($sender, $item));
		return;

		switch (true) {
			case $rh >= RS::RANK_HIERARCHY["sr_mod"]:
				$cooldown = 0;
				break;
			case $rh >= RS::RANK_HIERARCHY["enderdragon"]:
				$cooldown = 60 * 30;
				break;
			case $rh >= RS::RANK_HIERARCHY["wither"]:
				$cooldown = 60 * 90;
				break;
			case $rh >= RS::RANK_HIERARCHY["enderman"]:
				$cooldown = 60 * 150;
				break;
			default:
				$cooldown = 60 * 240;
				break;
		}
		$ench->setCooldown($sender, $cooldown);

		$item->setDamage(0);
		$sender->getInventory()->setItemInHand($item);
		$sender->sendMessage(TextFormat::GI . "Successfully repaired the item in your hand!");
		return true;
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}

<?php

namespace skyblock\techits\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player,
	techits\item\TechitNote as TechitNoteItem
};

use core\Core;
use core\discord\objects\{
	Post,
	Webhook,
	Embed,
	Field,
	Footer
};
use core\utils\ItemRegistry;
use core\utils\TextFormat;

class TechitNote extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/** @param Player $sender */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args) {
		$amount = (int) array_shift($args);

		if ($sender->getName() === "xJustJqy") { // leaving this for nostalgia purposes hyuckles :3
			$sender->sendMessage(TextFormat::RI . "Fock off m8");
			return;
		}

		if ($amount <= 0) {
			$sender->sendMessage(TextFormat::RN . "Amount must be at least 1!");
			return;
		}
		if ($amount > 500000000) {
			$sender->sendMessage(TextFormat::RN . "Amount must be less than 50,000,000!");
			return;
		}

		if ($amount > $sender->getTechits()) {
			$sender->sendMessage(TextFormat::RN . "You do not have enough Techits!");
			return;
		}

		$item = ItemRegistry::TECHIT_NOTE();
		$item->setup($sender, $amount);
		if (!$sender->getInventory()->canAddItem($item)) {
			$sender->sendMessage(TextFormat::RN . "Your inventory is full!");
			return;
		}

		$sender->getInventory()->addItem($item);
		$before = $sender->getTechits();
		$sender->takeTechits($amount);
		$after = $sender->getTechits();
		$sender->sendMessage(TextFormat::GN . "Techit Note added to your inventory!");

		$post = new Post("", "Pay Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $sender->getName() . "** just created a Techit Note worth **" . number_format($amount) . " techits**", "", "ffb106", new Footer("Joe | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field("Before", number_format($before), true),
				new Field("After", number_format($after), true),
				new Field("Note ID", $item->getNamedTag()->getString('techitID', "NONE (Old note)"), true)
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("skyblock-paylog"));
		$post->send();
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}

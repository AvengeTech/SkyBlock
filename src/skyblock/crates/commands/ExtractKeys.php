<?php

namespace skyblock\crates\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\crates\item\KeyNote;

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

class ExtractKeys extends CoreCommand {

	public function __construct(public SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["keynote"]);
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args) {
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RN . "Usage: /extractkeys <type> [amount]");
			return;
		}

		$type = strtolower(array_shift($args));
		$amount = 1;
		if (isset($args[0])) $amount = (int) array_shift($args);

		if ($amount <= 0) {
			$sender->sendMessage(TextFormat::RN . "Amount must be at least 1!");
			return;
		}

		if (!in_array($type, ["iron", "gold", "diamond", "emerald", "vote", "divine"])) {
			$sender->sendMessage(TextFormat::RN . "Invalid key type!");
			return;
		}

		$colors = [
			"iron" => TextFormat::WHITE,
			"gold" => TextFormat::GOLD,
			"diamond" => TextFormat::AQUA,
			"emerald" => TextFormat::GREEN,
			"vote" => TextFormat::YELLOW,
			"divine" => TextFormat::RED,
		];

		$session = $sender->getGameSession()->getCrates();
		if ($session->isOpening()) {
			$sender->sendMessage(TextFormat::RI . "You cannot run this command while opening a crate!");
			return;
		}
		$keys = $session->getKeys($type);
		if ($keys < $amount) {
			$sender->sendMessage(TextFormat::RN . "You do not have " . $colors[$type] . $amount . " " . ucfirst($type) . " Keys" . TextFormat::GRAY . " to extract!");
			return;
		}

		$note = ItemRegistry::KEY_NOTE();
		$note->setup($sender, $type, $amount);

		if (!$sender->getInventory()->canAddItem($note)) {
			$sender->sendMessage(TextFormat::RN . "Your inventory is full! Please make room before extracting keys!");
			return;
		}

		$before = $session->getKeys($type);
		$session->takeKeys($type, $amount);
		$after = $session->getKeys($type);
		$sender->getInventory()->addItem($note);

		$post = new Post("", "Key Note Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $sender->getName() . "** just created a Key Note worth **" . number_format($amount) . " " . ucfirst($type) . " keys**", "", "ffb106", new Footer("Joe | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field("Before", number_format($before), true),
				new Field("After", number_format($after), true),
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("keynotes-skyblock"));
		$post->send();

		$sender->sendMessage(TextFormat::GN . "Successfully extracted " . $colors[$type] . number_format($amount) . " " . ucfirst($type) . " Keys!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}

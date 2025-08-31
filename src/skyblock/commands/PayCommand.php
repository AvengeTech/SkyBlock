<?php

namespace skyblock\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use core\discord\objects\{
	Post,
	Webhook,
	Embed,
	Field,
	Footer
};
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;
use core\Core;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class PayCommand extends CoreCommand {

	const PAY_COOLDOWN = 15;

	public function __construct(public SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args) {
		if (count($args) != 2) {
			$sender->sendMessage(TextFormat::RN . "Usage: /pay <name> <amount>");
			return;
		}
		/** @var SkyBlockPlayer $player */
		$player = $this->plugin->getServer()->getPlayerExact(array_shift($args));
		$techits = (int)array_shift($args);

		if (!$player instanceof Player || !$player->isLoaded()) {
			$sender->sendMessage(TextFormat::RN . "You must be near this player to pay them!");
			return;
		}
		if ($player === $sender) {
			$sender->sendMessage(TextFormat::RI . "You cannot pay yourself!");
			return;
		}
		$ptechits = $sender->getTechits();

		if (isset($this->plugin->paycd[$sender->getName()])) {
			if (($left = $this->plugin->paycd[$sender->getName()] - time()) > 0) {
				$sender->sendMessage(TextFormat::RI . "You must wait " . TextFormat::YELLOW . $left . TextFormat::GRAY . " more seconds to send another payment!");
				return;
			}
		}

		if ($techits <= 0) {
			$sender->sendMessage(TextFormat::RN . "You cannot pay players under 0 techits!");
			return;
		}

		if ($techits > $ptechits) {
			$sender->sendMessage(TextFormat::RN . "You do not have enough techits to pay this amount!");
			return;
		}

		$before = $player->getTechits();
		$player->addTechits($techits);
		$after = $player->getTechits();

		$sbefore = $sender->getTechits();
		$sender->takeTechits($techits);
		$safter = $sender->getTechits();

		$this->plugin->paycd[$sender->getName()] = time() + self::PAY_COOLDOWN;

		$player->sendMessage(TextFormat::GN . "You received " . TextFormat::AQUA . number_format($techits) . " techits" . TextFormat::GRAY . " from " . TextFormat::YELLOW . $sender->getName());
		$sender->sendMessage(TextFormat::GN . "Successfully sent " . TextFormat::AQUA . number_format($techits) . " techits" . TextFormat::GRAY . " to " . TextFormat::YELLOW . $player->getName());

		$server = Core::getInstance()->getNetwork()->getServerManager()->getThisServer();

		$post = new Post("", "Pay Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $sender->getName() . "** just sent **" . number_format($techits) . " techits** to **" . $player->getName() . "**", "", "ffb106", new Footer("Peanut butter! | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field($player->getName() . "'s balance", number_format($before) . " -> " . number_format($after), true),
				new Field($sender->getName() . "'s balance", number_format($sbefore) . " -> " . number_format($safter), true),
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("skyblock-paylog"));
		$post->send();
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}

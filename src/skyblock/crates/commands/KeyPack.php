<?php

namespace skyblock\crates\commands;

use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;

use core\Core;
use core\discord\objects\{
	Post,
	Webhook,
	Embed,
	Field,
	Footer
};
use core\rank\Rank;
use core\user\User;
use core\utils\TextFormat;

class KeyPack extends CoreCommand {

	const PACKS = [
		"small" => [
			"iron" => 15,
			"gold" => 15,
			"diamond" => 10,
			"emerald" => 5,
		],
		"medium" => [
			"iron" => 25,
			"gold" => 25,
			"diamond" => 15,
			"emerald" => 10,
		],
		"large" => [
			"iron" => 45,
			"gold" => 45,
			"diamond" => 35,
			"emerald" => 25,
		],
		"extra-large" => [
			"iron" => 100,
			"gold" => 100,
			"diamond" => 50,
			"emerald" => 50,
			"divine" => 2
		],

	];

	public function __construct(public SkyBlock $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		if (count($args) != 2) {
			$sender->sendMessage(TextFormat::RN . "Usage: /keypack <player> <type>");
			return;
		}

		$name = array_shift($args);
		$type = strtolower(array_shift($args));

		$player = $this->plugin->getServer()->getPlayerExact($name);
		if ($player instanceof Player) {
			$name = $player->getName();
		}

		if (!in_array($type, ["small", "medium", "large", "extra-large"])) {
			$sender->sendMessage(TextFormat::RN . "Invalid key pack type! (small, medium, large, extra large)");
			return;
		}

		$keys = self::PACKS[$type];

		Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender, $player, $type, $keys): void {
			if ($sender instanceof Player && !$sender->isConnected()) return;
			if (!$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function (SkyBlockSession $session) use ($sender, $player, $type, $keys): void {
				if ($sender instanceof Player && !$sender->isConnected()) return;
				foreach ($keys as $t => $amount)
					$session->getCrates()->addKeys($t, $amount);
				if ($player instanceof Player && $player->isConnected()) {
					$player->sendMessage(TextFormat::GI . "You have received a " . TextFormat::YELLOW . $type . " key pack!");
				} else {
					$session->getCrates()->saveAsync();
				}
				$sender->sendMessage(TextFormat::GN . "Successfully gave a " . $type . " key pack to " . $session->getUser()->getGamertag() . "!");
				$post = new Post("", "Key Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
					new Embed("", "rich", "**" . $sender->getName() . "** just gave a " . $type . " key pack to " . $session->getUser()->getGamertag() . "!", "", "ffb106", new Footer("ok | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [])
				]);
				$post->setWebhook(Webhook::getWebhookByName("keynotes-skyblock"));
				$post->send();
			});
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}

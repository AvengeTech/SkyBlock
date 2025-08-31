<?php

namespace skyblock\games\coinflips\ui;

use core\Core;
use core\discord\objects\Author;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Footer;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};

use core\ui\elements\customForm\{
	Label,
	Input
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class CreateCoinflipUi extends CustomForm {

	public function __construct(Player $player, string $message = "", bool $error = true) {
		if (!($player instanceof SkyBlockPlayer)) return;
		parent::__construct("Create Coinflip");
		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . PHP_EOL . PHP_EOL . TextFormat::RESET : "") .
				"Enter the amount of techits you'd like to place on this coin flip (at least 1,000!) You have " . TextFormat::AQUA . number_format($player->getTechits()) . " techits " . TextFormat::WHITE . "available."
		));
		$this->addElement(new Input("Techits", "0"));
		$this->addElement(new Label("By pressing submit, you will be paying the amount of techits provided above, for a chance to win " . TextFormat::GREEN . "double" . TextFormat::WHITE . ". Techits will be returned if you leave the server."));
	}

	public function close(Player $player) {
		if (!($player instanceof SkyBlockPlayer)) return;
		$player->showModal(new CoinflipsUi());
	}

	public function handle($response, Player $player) {
		if (!($player instanceof SkyBlockPlayer)) return;
		$techits = (int) $response[1];
		if ($techits <= 1000) {
			$player->showModal(new CreateCoinflipUi($player, "Must spend at least 1,000 techits"));
			return;
		}
		if ($player->getTechits() < $techits) {
			$player->showModal(new CreateCoinflipUi($player, "You don't have enough techits!"));
			return;
		}
		$cf = SkyBlock::getInstance()->getGames()->getCoinflips();
		$bcf = $player->getTechits();
		$cf->newGame($player, $techits);
		$acf = $player->getTechits();
		$player->showModal(new CoinflipsUi("Successfully created coinflip!", false));
		$type = Core::getInstance()->getNetwork()->getServerType();
		$post = new Post("", "Coinflip - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $player->getName() . "** created a new coinflip worth **" . number_format($techits) . "**!", "", "ffb106", new Footer("Gambling addicts smh | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field("Before", number_format($bcf), true),
				new Field("After", number_format($acf), true)
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("coinflips-" . $type));
		$post->send();
	}
}

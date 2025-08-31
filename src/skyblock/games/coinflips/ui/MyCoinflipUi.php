<?php

namespace skyblock\games\coinflips\ui;

use core\Core;
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
use skyblock\games\coinflips\Coinflip;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

use core\utils\TextFormat;

class MyCoinflipUi extends SimpleForm {

	public function __construct(public Coinflip $coinflip) {
		parent::__construct(
			"My coinflip",
			"Value: " . TextFormat::AQUA . number_format($this->coinflip->getValue())
		);
		$this->addButton(new Button("Cancel coinflip"));
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		if (!($player instanceof SkyBlockPlayer)) return;
		if ($response === 1) {
			$player->showModal(new CoinflipsUi());
			return;
		}
		$flip = $this->coinflip;
		$cf = SkyBlock::getInstance()->getGames()->getCoinflips();
		if ($cf->getGame($flip->getId()) === null || !$flip->getPlayer()->isConnected()) {
			$player->showModal(new CoinflipsUi("That coinflip no longer exists!"));
			return;
		}
		$bcf = $player->getTechits();
		$flip->cancel();
		$acf = $player->getTechits();
		unset($cf->games[$flip->getId()]);
		$player->showModal(new CoinflipsUi("Coinflip has been cancelled", false));
		$type = Core::getInstance()->getNetwork()->getServerType();
		$post = new Post("", "Coinflip - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $player->getName() . "** cancelled a coinflip worth **" . number_format($flip->getValue()) . "**!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field("Before", number_format($bcf), true),
				new Field("After", number_format($acf), true)
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("coinflips-" . $type));
		$post->send();
	}
}

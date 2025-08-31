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

class CoinflipConfirmUi extends SimpleForm {

	public function __construct(public Coinflip $coinflip) {
		parent::__construct(
			"Confirm coinflip",
			"You are about to pay " . number_format($coinflip->getValue()) . " techits to flip a coin, for a chance to double your techits! Are you sure you'd like to continue?"
		);
		$this->addButton(new Button("Flip coin!"));
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
		if ($player->getTechits() < $flip->getValue()) {
			$player->showModal(new CoinflipsUi($player, "You don't have enough techits!"));
			return;
		}
		$bcf = $player->getTechits();
		$bcf2 = $flip->getPlayer()->getTechits();
		$win = $flip->play($player);
		$acf = $player->getTechits();
		$acf2 = $flip->getPlayer()->getTechits();
		unset($cf->games[$flip->getId()]);
		if ($win) {
			$player->showModal(new CoinflipsUi("You won coinflip against " . $flip->getPlayer()->getName() . " for " . TextFormat::AQUA . number_format($flip->getValue()) . " techits!", false));
			$type = Core::getInstance()->getNetwork()->getServerType();
			$post = new Post("", "Coinflip - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $player->getName() . "** won a coinflip worth **" . number_format($flip->getValue()) . "**!", "", "ffb106", new Footer("O'moy gawrsh | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field("Before", number_format($bcf), true),
					new Field("After", number_format($acf), true),
					new Field("Created By", $flip->getPlayer()->getName(), true)
				]),
				new Embed("", "rich", "**" . $flip->getPlayer()->getName() . "** lost a coinflip worth **" . number_format($flip->getValue()) . "**!", "", "ffb106", new Footer("What a goof | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field("Before", number_format($bcf2), true),
					new Field("After", number_format($acf2), true)
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("coinflips-" . $type));
			$post->send();
		} else {
			$player->showModal(new CoinflipsUi("You lost coinflip against " . $flip->getPlayer()->getName() . " for " . TextFormat::AQUA . number_format($flip->getValue()) . " techits!"));
			$type = Core::getInstance()->getNetwork()->getServerType();
			$post = new Post("", "Coinflip - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $player->getName() . "** lost a coinflip worth **" . number_format($flip->getValue()) . "**!", "", "ffb106", new Footer("What a goof | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field("Before", number_format($bcf), true),
					new Field("After", number_format($acf), true),
					new Field("Created By", $flip->getPlayer()->getName(), true)
				]),
				new Embed("", "rich", "**" . $flip->getPlayer()->getName() . "** won a coinflip worth **" . number_format($flip->getValue()) . "**!", "", "ffb106", new Footer("O'moy gawrsh | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field("Before", number_format($bcf2), true),
					new Field("After", number_format($acf2), true),
				]),
			]);
			$post->setWebhook(Webhook::getWebhookByName("coinflips-" . $type));
			$post->send();
		}
	}
}

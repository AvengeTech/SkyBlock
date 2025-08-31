<?php

namespace skyblock\crates\ui;

use core\AtPlayer;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer as Player;

class RemoteCratesUi extends SimpleForm {

	public function __construct(Player $player) {
		$session = $player->getGameSession()->getCrates();

		$colors = [
			"iron" => TextFormat::WHITE,
			"gold" => TextFormat::GOLD,
			"diamond" => TextFormat::AQUA,
			"emerald" => TextFormat::GREEN,
			"vote" => TextFormat::YELLOW,
			"divine" => TextFormat::RED
		];
		$keys = "";
		foreach ($session->getAllKeys() as $type => $amount) {
			$keys .= $colors[$type] . number_format($amount) . " " . ucfirst($type) . " keys" . PHP_EOL;
		}

		parent::__construct("Crates", "Your keys:" . PHP_EOL . PHP_EOL . $keys . PHP_EOL . TextFormat::RESET . TextFormat::WHITE . "Select a crate to open:" . PHP_EOL);

		$this->addButton(new Button(TextFormat::BOLD . TextFormat::YELLOW . "Vote Crate"));
		$this->addButton(new Button(TextFormat::BOLD . TextFormat::RED . "Divine Crate"));
		$this->addButton(new Button(TextFormat::BOLD . TextFormat::GREEN . "Emerald Crate"));
		$this->addButton(new Button(TextFormat::BOLD . TextFormat::AQUA . "Diamond Crate"));
		$this->addButton(new Button(TextFormat::BOLD . TextFormat::GOLD . "Gold Crate"));
		$this->addButton(new Button(TextFormat::BOLD . TextFormat::WHITE . "Iron Crate"));
		$this->addButton(new Button(TextFormat::BOLD . TextFormat::DARK_RED . "Close"));
	}

	public function handle($response, AtPlayer $player) {
		switch ($response) {
			case 0:
				$player->showModal(new OpenCrateUi($player, SkyBlock::getInstance()->getCrates()->getCrateById(4), true));
				break;
			case 1:
				$player->showModal(new OpenCrateUi($player, SkyBlock::getInstance()->getCrates()->getCrateById(9), true));
				break;
			case 2:
				$player->showModal(new OpenCrateUi($player, SkyBlock::getInstance()->getCrates()->getCrateById(3), true));
				break;
			case 3:
				$player->showModal(new OpenCrateUi($player, SkyBlock::getInstance()->getCrates()->getCrateById(2), true));
				break;
			case 4:
				$player->showModal(new OpenCrateUi($player, SkyBlock::getInstance()->getCrates()->getCrateById(1), true));
				break;
			case 5:
				$player->showModal(new OpenCrateUi($player, SkyBlock::getInstance()->getCrates()->getCrateById(0), true));
				break;
			default:
				return;
		}
	}
}

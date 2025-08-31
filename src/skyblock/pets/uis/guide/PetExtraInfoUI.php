<?php

namespace skyblock\pets\uis\guide;

use core\AtPlayer;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat as TF;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\pets\Structure;
use skyblock\pets\types\PetData;

class PetExtraInfoUI extends SimpleForm{

	public function __construct(
		private int $identifier
	){
		$data = new PetData($identifier, Structure::PETS[$identifier][Structure::DATA_NAME]);

		$content = TF::GRAY . "Max Level: " . TF::WHITE . $data->getMaxLevel() . "\n";
		$content .= TF::GRAY . "Rarity: " . ED::rarityColor($data->getRarity()) . ED::rarityName($data->getRarity()) . "\n";
		$content .= TF::GRAY . "Descriptions:";
		
		foreach([1, 10, 20, 30, 40, 50] as $level){
			$content .= TF::BOLD . TF::AQUA . "\nLevel " . TF::GOLD . $level . TF::RESET . TF::WHITE . " - " . str_replace("%", "%%%", $data->getDescription($level));
		}

		parent::__construct($data->getDefaultName(), $content);

		$this->addButton(new Button(TF::BLUE . "Back"));
		$this->addButton(new Button(TF::RED . "Close"));
	}

	public function handle($response, AtPlayer $player){
		if($response === 0){
			$player->showModal(new PetGuideUI());
		}
	}
}
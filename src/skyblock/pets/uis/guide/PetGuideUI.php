<?php

namespace skyblock\pets\uis\guide;

use core\AtPlayer;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\pets\Structure;

class PetGuideUI extends SimpleForm{

	public function __construct(){
		$content = TF::BOLD . TF::LIGHT_PURPLE . "What are Pets?\n";
		$content .= TF::RESET . TF::WHITE . "Pets are entities that give you small buffs to help you progress.\n\n";
		$content .= TF::BOLD . TF::LIGHT_PURPLE . "How do I get a Pet?\n";
		$content .= TF::RESET . TF::WHITE . "You get pets by using a Pet Key on a Pet Box. ";
		$content .= TF::RESET . TF::WHITE . "Once opened, you will receive a random Pet Egg.\n\n";
		$content .= TF::BOLD . TF::LIGHT_PURPLE . "How do I get a Pet Box?\n";
		$content .= TF::RESET . TF::WHITE . "You obtain Pet Boxes every 5 island levels, from the Capsule enchantment, or through opening ";
		$content .= TF::RESET . TF::AQUA . "Supply Drops" . TF::WHITE . " which spawn in " . TF::BOLD . TF::RED . "WARZONE!" . TF::RESET . TF::WHITE . " (Be Careful)\n\n";
		$content .= TF::BOLD . TF::LIGHT_PURPLE . "How do I get a Pet Key?\n";
		$content .= TF::RESET . TF::WHITE . "You obtain Pet Keys by fishing in lava with the Thermal Hook enchantment, from the ";
		$content .= TF::RESET . TF::WHITE . "Capsule enchantment, or from the KOTH reward pouch.\n\n";
		$content .= TF::BOLD . TF::LIGHT_PURPLE . "How do I level up my Pet?\n";
		$content .= TF::RESET . TF::WHITE . "To gain experience for your pet you need to mine ore, fish, slay mobs, or farm crops. You can ";
		$content .= TF::RESET . TF::WHITE . "also obtain an item called a " . ItemRegistry::GUMMY_ORB()->setup(1)->init()->getCustomName();
		$content .= TF::RESET . TF::WHITE . " in order to give your pet some quick experience!\n\n";
		$content .= TF::BOLD . TF::LIGHT_PURPLE . "How many pets are there?\n";
		$content .= TF::RESET . TF::WHITE . "Currently there are a total of 8 pets. (Axolotl, Allay, Bee, Cat, Dog, Fox, Rabbit, and Vex)\n\n";
		$content .= TF::BOLD . TF::LIGHT_PURPLE . "What buffs do the pets give?\n";
		$content .= TF::RESET . TF::WHITE . "Click a button below to check what that pet does.";

		parent::__construct("Pets Guide", $content);

		foreach(Structure::PETS as $data){
			$this->addButton(new Button(ED::rarityColor($data[Structure::DATA_RARITY]) . $data[Structure::DATA_NAME]));
		}

		$this->addButton(new Button(TF::DARK_RED . TF::BOLD . "Close"));
	}

	public function handle($response, AtPlayer $player){
		if($response === 8) return;

		$player->showModal(new PetExtraInfoUI(($response + 1)));
	}
}
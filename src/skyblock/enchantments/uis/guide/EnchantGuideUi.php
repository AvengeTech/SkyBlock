<?php namespace skyblock\enchantments\uis\guide;

use pocketmine\player\Player;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

use skyblock\SkyBlockPlayer;

class EnchantGuideUi extends SimpleForm{

	public function __construct(){
		parent::__construct("Enchantment Guide", "Welcome to the enchantment guide. Each enchantment in this guide can be earned in crates or fished." . PHP_EOL . PHP_EOL . "Overclockable enchantments have a chance to become " . TextFormat::YELLOW . "1 level higher" . TextFormat::WHITE . " than the max level by prestiging your tools in the /tree menu" . PHP_EOL . PHP_EOL . "Select a rarity to see all belonging enchantments!");

		$this->addButton(new Button("Common"));
		$this->addButton(new Button("Uncommon"));
		$this->addButton(new Button("Rare"));
		$this->addButton(new Button("Legendary"));
		$this->addButton(new Button("Divine"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$player->showModal(new GuideSelectUi($player, $response + 1));
	}

}
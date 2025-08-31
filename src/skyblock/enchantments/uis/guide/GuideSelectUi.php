<?php namespace skyblock\enchantments\uis\guide;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\enchantments\EnchantmentData as ED;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\utils\TextFormat;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\SkyBlockPlayer;

class GuideSelectUi extends SimpleForm{

	public $guides = [];

	public function __construct(Player $player, int $rarity){
		/** @var SkyBlockPlayer $player */
		$e = array_values(EnchantmentRegistry::getEnchantments($rarity));
		$en = $e[0];

		parent::__construct($en->getRarityName() . " enchantments", "Select an enchantment below to get it's description!");

		$key = 0;
		foreach($e as $en){
			if(!$en->isDisabled() || $player->isStaff()){
				$this->guides[$key] = $en;
				$key++;
				$this->addButton(new Button($en->getRarityColor() . $en->getName() . ($en->isDisabled() ? " " . TextFormat::BOLD . TextFormat::RED . "(Disabled)" . TextFormat::RESET : "") . TextFormat::DARK_GRAY . "\n" . $en->getTypeName() . " enchantment"));
			}
		}

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		foreach($this->guides as $key => $guide){
			if($response == $key){
				$player->showModal(new ShowGuideUi($guide, $this));
				return;
			}
		}
		$player->showModal(new EnchantGuideUi());
	}

}
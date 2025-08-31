<?php namespace skyblock\enchantments\uis\guide;

use core\ui\CustomUI;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use skyblock\enchantments\type\{
	Enchantment,
	ArmorEnchantment
};

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use skyblock\SkyBlockPlayer;

class ShowGuideUi extends SimpleForm{

	public $prevpage;

	public function __construct(Enchantment $enchantment, $prev = null){
		$this->prevpage = $prev;
		parent::__construct($enchantment->getName(),
			TextFormat::clean($enchantment->getRarityName()) . " " . $enchantment->getTypeName() . " enchantment" . PHP_EOL . PHP_EOL .
			"Max level: " . $enchantment->getMaxLevel() . ($enchantment->canOverclock() ? " (CAN OVERCLOCK +1)" : "") .
			($enchantment instanceof ArmorEnchantment ? PHP_EOL . ($enchantment->isStackable() ? "Stackable: YES" . PHP_EOL . "Max stack level: " . $enchantment->getMaxStackLevel() : "Stackable: NO") : "") . PHP_EOL . PHP_EOL .
			"Description: " . $enchantment->getDescription()
		);
		if($prev !== null) $this->addButton(new Button("Back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if (!$this->prevpage instanceof CustomUI) return;
		$player->showModal($this->prevpage);
	}

}
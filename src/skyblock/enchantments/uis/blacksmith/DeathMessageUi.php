<?php namespace skyblock\enchantments\uis\blacksmith;

use pocketmine\player\Player;
use pocketmine\item\Sword;

use skyblock\enchantments\ItemData;
use skyblock\enchantments\item\CustomDeathTag;
use skyblock\item\NetheriteSword;

use core\chat\Chat;
use core\ui\elements\customForm\{
	Label,
	Dropdown,
	Input
};
use core\ui\windows\CustomForm;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use skyblock\SkyBlockPlayer;

class DeathMessageUi extends CustomForm{

	public $items = [];

	public function __construct(Player $player){
		parent::__construct("Add Death Message");

		$this->addElement(new Label("What item would you like to add a death message to?"));

		$dropdown = new Dropdown("Item selection");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item instanceof Sword){
				$this->items[$key] = $item;
				$dropdown->addOption($item->getName() . TextFormat::RESET . TextFormat::WHITE . ($item->hasEnchantments() ? " (" . count($item->getEnchantments()) . " enchantments)" : ""));
				$key++;
			}
		}
		$this->addElement($dropdown);

		$this->addElement(new Label("What death message would you like to add?"));
		$this->addElement(new Input("Death Message", "murdered"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if(empty($this->items)) return;

		$cd = ItemRegistry::CUSTOM_DEATH_TAG();
		$cd->init();
		$cd = $player->getInventory()->first($cd);
		if($cd == -1){
			$player->sendMessage(TextFormat::RN . "Your inventory must contain a " . TextFormat::YELLOW . "Custom Death Tag" . TextFormat::GRAY . " to do this!");
			return;
		}

		$item = $this->items[$response[1]];
		$data = new ItemData($item);
		if(!$data->canEdit()){
			$player->sendMessage(TextFormat::RN . "This item cannot be edited!");
			return;
		}

		$text = $response[3];
		$mbl = mb_strlen($text);
		$mbl += substr_count($text, TextFormat::ESCAPE);
		if($mbl != strlen($text) && !$player->hasRank()){
			$player->sendMessage(TextFormat::YN . "You cannot use unicode characters without a rank!");
			return;
		}
		$text = $player->hasRank() ? Chat::convertWithEmojis($text) : $text;
		if(strlen($text) > 50){
			$player->sendMessage(TextFormat::YN . "Death Message must be less than 50 characters!");
			return;
		}

		$player->showModal(new ConfirmDeathUi($item, $text));
	}

}
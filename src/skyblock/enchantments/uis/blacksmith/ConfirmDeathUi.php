<?php namespace skyblock\enchantments\uis\blacksmith;

use pocketmine\player\Player;
use pocketmine\item\Item;

use skyblock\enchantments\item\CustomDeathTag;
use skyblock\enchantments\ItemData;

use core\ui\windows\ModalWindow;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use skyblock\SkyBlockPlayer;

class ConfirmDeathUi extends ModalWindow{

	public $item;
	public $text;

	public $price;

	public function __construct(Item $item, $text){
		$this->item = $item;
		$this->text = $text;

		$this->price = strlen($text);

		parent::__construct("Confirm Death Message", "Adding this death message to this item will cost " . TextFormat::YELLOW . $this->price . " XP Levels" . TextFormat::WHITE . ", are you sure you want to add the death message '" . $this->text . TextFormat::RESET . "'?", "Add Death Message", "Go back");
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if($response){
			$cd = ItemRegistry::CUSTOM_DEATH_TAG();
			$cd->init();
			$cd = $player->getInventory()->first($cd);
			if($cd == -1){
				$player->sendMessage(TextFormat::RN . "Your inventory must contain a " . TextFormat::YELLOW . "Custom Death Tag" . TextFormat::GRAY . " to do this!");
				return;
			}

			$item = $player->getInventory()->first($this->item, true);
			if($item == -1){
				$player->sendMessage(TextFormat::RN . "Item you're trying to add a death message to no longer exists in inventory!");
				return;
			}

			if($player->getXpManager()->getXpLevel() < $this->price){
				$player->sendMessage(TextFormat::RN . "You do not have enough XP Levels to add this death message!");
				return;
			}

			$slot = $item;
			$item = $player->getInventory()->getItem($slot);
			$data = new ItemData($item);
			$data->setDeathMessage($this->text);
			$player->getInventory()->setItem($slot, $data->getItem());

			$dt = $player->getInventory()->getItem($cd);
			$dt->pop();
			$player->getInventory()->setItem($cd, $dt);

			$player->getXpManager()->subtractXpLevels($this->price);
			$player->sendMessage(TextFormat::GI . "Successfully added the death message '" . $this->text . TextFormat::RESET . TextFormat::GRAY . "' to this item!");
		}else{
			$player->showModal(new DeathMessageUi($player));
		}
	}

}
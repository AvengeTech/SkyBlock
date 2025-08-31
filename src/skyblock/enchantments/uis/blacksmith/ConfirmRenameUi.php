<?php namespace skyblock\enchantments\uis\blacksmith;

use pocketmine\player\Player;
use pocketmine\item\Item;

use skyblock\enchantments\item\Nametag;

use core\ui\windows\ModalWindow;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use skyblock\SkyBlockPlayer;

class ConfirmRenameUi extends ModalWindow{

	public $item;
	public $text;

	public $price;

	public function __construct(Item $item, $text){
		$this->item = $item;
		$this->text = $text;

		$this->price = strlen($text);

		parent::__construct("Confirm rename", "Renaming this item will cost " . TextFormat::YELLOW . $this->price . " XP Levels" . TextFormat::WHITE . ", are you sure you want to rename your item to " . $this->text, "Rename Item", "Go back");
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if($response){
			$nt = ItemRegistry::NAMETAG();
			$nt->init();
			$nt = $player->getInventory()->first($nt);
			if($nt == -1){
				$player->sendMessage(TextFormat::RN . "Your inventory must contain a " . TextFormat::AQUA . "Nametag" . TextFormat::GRAY . " to do this!");
				return;
			}

			$item = $player->getInventory()->first($this->item, true);
			if($item == -1){
				$player->sendMessage(TextFormat::RN . "Item you're trying to rename no longer exists in inventory!");
				return;
			}

			if($player->getXpManager()->getXpLevel() < $this->price){
				$player->sendMessage(TextFormat::RN . "You do not have enough XP Levels to rename this!");
				return;
			}

			$slot = $item;
			$item = $player->getInventory()->getItem($slot);
			$item->setCustomName(TextFormat::RESET . $this->text);
			$player->getInventory()->setItem($slot, $item);

			$nametag = $player->getInventory()->getItem($nt);
			$nametag->pop();
			$player->getInventory()->setItem($nt, $nametag);

			$player->getXpManager()->subtractXpLevels($this->price);
			$player->sendMessage(TextFormat::GI . "Successfully renamed your item to " . $this->text);
		}else{
			$player->showModal(new RenameItemUi($player));
		}
	}

}
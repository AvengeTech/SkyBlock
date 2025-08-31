<?php namespace skyblock\shop\ui;

use pocketmine\{
	player\Player,
};
use core\block\tile\Chest;

use skyblock\SkyBlock;

use core\ui\windows\ModalWindow;
use core\utils\ItemRegistry;
use core\utils\TextFormat;

class SellChestConfirmUi extends ModalWindow{
	
	public function __construct(Player $player, public Chest $chest, public bool $wand = false){
		$data = SkyBlock::getInstance()->getShops()->sellChest($player, $chest, false);

		parent::__construct(
			"Confirm Sell Chest",
			"Are you sure you want to sell " . $data["count"] . " items in this chest for " . $data["price"] . " techits?",
			"Sell " . $data["count"] . " items",
			"Cancel"
		);
	}

	public function handle($response, Player $player){
		if($response){
			$data = SkyBlock::getInstance()->getShops()->sellChest($player, $this->chest, false);
			if($data["price"] <= 0){
				$player->sendMessage(TextFormat::RI . "Nothing in this chest can be sold!");
				return;
			}
			if($this->wand){
				$sw = ItemRegistry::SELL_WAND();
				$sw->init();
				if(!$player->getInventory()->contains($sw)){
					$player->sendMessage(TextFormat::RI . "You no longer have a Sell Wand in your inventory!");
					return;
				}
			}
			if(!(($chest = $this->chest)->isClosed())){
				$data = SkyBlock::getInstance()->getShops()->sellChest($player, $chest);
				if($this->wand) $player->getInventory()->removeItem($sw);
				$player->sendMessage(TextFormat::GI . "Sold " . TextFormat::YELLOW . $data["count"] . TextFormat::GRAY . " items for " . TextFormat::AQUA . $data["price"] . " Techits");
				return;
			}
			$player->sendMessage(TextFormat::RI . "Chest no longer exists. Try again?");
		}
	}

}
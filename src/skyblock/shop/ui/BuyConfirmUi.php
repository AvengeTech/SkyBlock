<?php namespace skyblock\shop\ui;

use pocketmine\{
	player\Player,
};

use skyblock\{
	SkyBlockPlayer
};
use skyblock\shop\data\ShopItem;
use skyblock\shop\event\{
	ShopBuyEvent,
	ShopSellEvent
};

use core\ui\windows\ModalWindow;
use core\utils\TextFormat;

class BuyConfirmUi extends ModalWindow{

	public float $price;

	public function __construct(
		public ShopItem $item,
		public int $amount,
		public int $type,
		public int $categoryId,
		public bool $back
	){
		$this->price = $price = ($type == 0 ? $item->getBuyPrice() : $item->getSellPrice()) * $amount;
		$word = ($type == 0 ? "buy" : "sell");
		$word2 = ($type == 0 ? "pay" : "receive");
		parent::__construct(
			"Confirm purchase",
			"Are you sure you want to " . $word . " x" . $amount . " " . $item->getName() . "? You will " . $word2 . " " . number_format($price, 2) . " techits total.",
			($type == 0 ? "Buy " : "Sell ") . "items",
			"Go back"
		);
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if($response){
			$item = $this->item;
			$amount = $this->amount;
			$type = $this->type;
			$price = $this->price;

			$i = clone $item->getItem();
			$i->setCount($amount);

			if($type == 0){
				if($player->getTechits() <= $this->price){
					$player->showModal(new ErrorUi(null, "You do not have enough techits for this trade!", "Error", $this->back));
					return;
				}
				$player->takeTechits($price);
				$player->getInventory()->addItem($i);
				$event = new ShopBuyEvent($item, $amount, $player);
			}else{
				if(!$player->getInventory()->contains($i)){
					$player->showModal(new ErrorUi(null, "Your inventory no longer contains the necessary items for this trade!", "Error", $this->back));
					return;
				}
				$player->addTechits($price);
				$player->getInventory()->removeItem($i);
				$event = new ShopSellEvent($item, $amount, $player);
			}
			$event->call();

			$player->sendMessage(TextFormat::GI . "You have successfully " . ($type == 0 ? "bought" : "sold") . " x" . $amount . " " . TextFormat::clean($item->getName()) . " for " . TextFormat::AQUA . number_format($price) . " techits");
			return;
		}
		$player->showModal(new CategoryUi($this->categoryId, $this->back));
	}

}
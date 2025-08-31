<?php namespace skyblock\shop\ui;

use pocketmine\player\Player;

use skyblock\shop\data\ShopItem;
use skyblock\shop\ui\ErrorUi;
use skyblock\{
	SkyBlockPlayer
};

use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Dropdown,
	Input
};
use core\utils\TextFormat;

class ItemDealUi extends CustomForm{

	public function __construct(
		public int $categoryId,
		public ShopItem $item,
		public bool $back
	){
		parent::__construct($item->getName());

		$this->addElement(new Label("Select how much of this item you are trying to buy or sell."));
		$this->addElement(new Input("Amount", 1, 1));
		$this->addElement(new Label("Select the type of deal you are trying to make"));
		$dropdown = new Dropdown("Deal type", [
			"Buy - " . ($item->canBuy() ? number_format($item->getBuyPrice(), 2) . " techit(s) per item" : "Cannot buy"),
			"Sell - " . ($item->canSell() ? number_format($item->getSellPrice(), 2) . " techit(s) per item" : "Cannot sell"),
		]);

		if(!$item->canBuy()){
			$dropdown->setIndexAsDefault(1);
		}

		$this->addElement($dropdown);

		$this->addElement(new Label("Tap the button below and you will be taken to a confirmation page"));
	}

	public function close(Player $player){
		/** @var SkyBlockPlayer $player */
		$player->showModal(new CategoryUi($this->categoryId, $this->back));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$item = $this->item;
		$amount = (int) $response[1];
		if($amount < 1){
			$player->showModal(new ErrorUi($this, "Amount must be a positive number!"));
			return;
		}
		$type = $response[3];

		if($type == 0 && !$item->canBuy()){
			$player->showModal(new ErrorUi($this, "This item cannot be bought!"));
			return;
		}
		if($type == 1 && !$item->canSell()){
			$player->showModal(new ErrorUi($this, "This item cannot be sold!"));
			return;
		}

		if($type == 0){
			$price = $item->getBuyPrice() * $amount;
			if($player->getTechits() < $price){
				$player->showModal(new ErrorUi($this, "You do not have enough techits to purchase this!", "Error", $this->back));
				return;
			}
			$i = clone $item->getItem();
			$i->setCount($amount);
			if(!$player->getInventory()->canAddItem($i)){
				$player->showModal(new ErrorUi($this, "Your inventory cannot hold this many items!", "Error", $this->back));
				return;
			}
		}elseif($type == 1){
			$i = clone $item->getItem();
			$i->setCount($amount);
			if(!$player->getInventory()->contains($i)){
				$player->showModal(new ErrorUi($this, "Your inventory doesn't contain this many items!", "Error", $this->back));
				return;
			}
		}

		$player->showModal(new BuyConfirmUi($item, $amount, $type, $this->categoryId, $this->back));
	}

}
<?php namespace skyblock\islands\shop\ui\view;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\islands\shop\{
	Shop,
	ShopItem
};

use core\ui\windows\ModalWindow;

use core\utils\TextFormat;

class ConfirmBuyShopItemUi extends ModalWindow{

	public int $price;

	public function __construct(Player $player, public Shop $shop, public ShopItem $shopItem, public int $quantity){
		parent::__construct(
			"Confirm purchase",
			"Are you sure you would like to buy x" . $this->quantity . " of " . $this->shopItem->getItem()->getName() . " for " . TextFormat::AQUA . number_format($this->price = $shopItem->getQuantityPrice($this->quantity)) . " techits" . TextFormat::WHITE . "?",
			"Purchase",
			"Go back"
		);
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = $player->getGameSession()->getIslands()->getIslandAt();
		if($island === null){
			$player->sendMessage(TextFormat::RI . "You are not at an island!");
			return;
		}
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if(!$perm->getPermission(Permissions::USE_SIGN_SHOPS)){
			$player->sendMessage(TextFormat::RI . "You don't have permission to create shops on this island!");
			return;
		}

		$shop = $this->shop->verify();
		if($shop === null){
			$player->sendMessage(TextFormat::RI . "This shop no longer exists!");
			return;
		}

		$item = $this->shopItem;
		if($response){
			if(!$item->verify($shop)){
				$player->showModal(new ShopFrontUi($player, $shop, "This item is no longer available for sale!"));
				return;
			}

			$quantity = $this->quantity;

			if($quantity > $item->getQuantity()){
				$player->showModal(new BuyShopItemUi($player, $shop, $item, "Not enough items in stock!"));
				return;
			}

			$price = $item->getQuantityPrice($quantity);
			if($price !== $this->price){
				$player->showModal(new BuyShopItemUi($player, $shop, $item, "Price of this item has changed!"));
				return;
			}

			if($player->getTechits() < $price){
				$player->showModal(new BuyShopItemUi($player, $shop, $item, "You cannot afford this!"));
				return;
			}

			if(!$item->canBuyQuantity($player, $quantity)){
				$player->showModal(new BuyShopItemUi($player, $shop, $item, "Your inventory is too full!"));
				return;
			}

			$item->buy($player, $quantity);

			$player->showModal(new ShopFrontUi($player, $shop, "Successfully bought x" . number_format($quantity) . " " . $item->getItem()->getName(), false));
		}else{
			$player->showModal(new BuyShopItemUi($player, $shop, $item));
		}
	}

}
<?php namespace skyblock\islands\shop\ui\view;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\islands\shop\{
	Shop,
	ShopItem
};

use core\ui\elements\customForm\{
	Label,
	Input
};
use core\ui\windows\CustomForm;

use core\utils\TextFormat;

class BuyShopItemUi extends CustomForm{

	public array $items = [];

	public function __construct(Player $player, public Shop $shop, public ShopItem $shopItem, string $message = "", bool $error = true){
		/** @var SkyBlockPlayer $player */
		parent::__construct("Buying " . $shopItem->getItem()->getName());

		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"You have " . TextFormat::AQUA . number_format($player->getTechits()) . " techit" . ($player->getTechits() !== 1 ? "s" : "") . TextFormat::WHITE . PHP_EOL .
			"This item costs "  . TextFormat::AQUA . number_format(($price = $shopItem->getPrice())) . " techit" . ($price !== 1 ? "s" : "") . TextFormat::WHITE . " each!" . PHP_EOL . PHP_EOL .
			"Enter the quantity you would like to purchase of this item! (x" . $shopItem->getQuantity() . " available)"
		));
		$this->addElement(new Input("Quantity", 1, 1));
	}

	public function close(Player $player){
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
		
		$player->showModal(new ShopFrontUi($player, $shop));
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
		if(!$item->verify($shop)){
			$player->showModal(new ShopFrontUi($player, $shop, "This item is no longer available for sale!"));
			return;
		}

		try {
			$quantity = (int)$response[1];
		} catch (\Exception $_) {
			$player->showModal(new BuyShopItemUi($player, $shop, $item, "Please provide a valid quantity!"));
			return;
		}

		if($quantity > $item->getQuantity()){
			$player->showModal(new BuyShopItemUi($player, $shop, $item, "Not enough items in stock!"));
			return;
		}
		
		if($player->getTechits() < $item->getQuantityPrice($quantity)){
			$player->showModal(new BuyShopItemUi($player, $shop, $item, "You cannot afford this!"));
			return;
		}

		if(!$item->canBuyQuantity($player, $quantity)){
			$player->showModal(new BuyShopItemUi($player, $shop, $item, "Your inventory is too full!"));
			return;
		}
		$player->showModal(new ConfirmBuyShopItemUi($player, $shop, $item, $quantity));
	}

}
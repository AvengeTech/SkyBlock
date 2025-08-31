<?php namespace skyblock\islands\shop\ui\view;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\islands\shop\Shop;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

use core\utils\TextFormat;

class ShopFrontUi extends SimpleForm{

	public array $items = [];

	public function __construct(Player $player, public Shop $shop, string $message = "", bool $error = true){
		parent::__construct(
			$shop->getName(),
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			$shop->getDescription() . TextFormat::RESET . TextFormat::WHITE . PHP_EOL . PHP_EOL .
			"Select an item you'd like to buy below!"
		);
		foreach($shop->getShopItems() as $item){
			$this->items[] = $item;
			$this->addButton(new Button(
				$item->getItem()->getName() . TextFormat::RESET . TextFormat::DARK_GRAY . " " . (($quantity = $item->getQuantity()) === 0 ? TextFormat::EMOJI_DENIED : "(x" . $quantity . ")") . PHP_EOL .
				number_format(($price = $item->getPrice())) . " techit" . ($price !== 1 ? "s" : "") . " each"
			));
		}
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

		$item = $this->items[$response];
		if(!$item->verify($shop)){
			$player->showModal(new ShopFrontUi($player, $shop, "This item is no longer available for sale!"));
			return;
		}
		if($item->getQuantity() === 0){
			$player->showModal(new ShopFrontUi($player, $shop, "This item is out of stock!"));
			return;
		}

		$player->showModal(new BuyShopItemUi($player, $shop, $item));
	}

}
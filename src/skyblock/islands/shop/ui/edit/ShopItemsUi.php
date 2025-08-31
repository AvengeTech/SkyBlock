<?php namespace skyblock\islands\shop\ui\edit;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\islands\shop\Shop;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

use core\utils\TextFormat;

class ShopItemsUi extends SimpleForm{

	public array $items = [];

	public function __construct(Player $player, public Shop $shop, string $message = "", bool $error = true){
		parent::__construct(
			"Edit items",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Select an option below!"
		);

		$this->addButton(new Button("Add item"));
		foreach($shop->getShopItems() as $item){
			$this->items[] = $item;
			$this->addButton(new Button(
				$item->getItem()->getName() . " " . (($quantity = $item->getQuantity()) === 0 ? TextFormat::EMOJI_DENIED : "(x" . $quantity . ")") . PHP_EOL .
				number_format(($price = $item->getPrice())) . " techit" . ($price !== 1 ? "s" : "") . " each"
			));
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = $player->getGameSession()->getIslands()->getIslandAt();
		if($island === null){
			$player->sendMessage(TextFormat::RI . "You are not at an island!");
			return;
		}
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if(!$perm->getPermission(Permissions::EDIT_SIGN_SHOPS)){
			$player->sendMessage(TextFormat::RI . "You don't have permission to create shops on this island!");
			return;
		}

		$shop = $this->shop->verify();
		if($shop === null){
			$player->sendMessage(TextFormat::RI . "This shop no longer exists!");
			return;
		}

		if($response === 0){
			if(count($shop->getShopItems()) >= $shop->getMaxShopItems()){
				$player->showModal(new ShopItemsUi($player, $shop, "Max amount of items for sale reached!"));
				return;
			}
			$player->showModal(new AddShopItemUi($player, $shop));
			return;
		}

		$item = $this->items[$response - 1] ?? null;
		if($item === null){
			$player->showModal(new EditShopUi($player, $shop));
			return;
		}
		if(!$item->verify($shop)){
			$player->showModal(new ShopItemsUi($player, $shop, "This item is no longer available for sale!"));
			return;
		}

		$player->showModal(new EditShopItemUi($player, $shop, $item));
	}

}
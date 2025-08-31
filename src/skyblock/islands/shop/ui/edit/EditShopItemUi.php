<?php namespace skyblock\islands\shop\ui\edit;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\islands\shop\{
	Shop,
	ShopItem
};

use core\ui\elements\customForm\{
	Label,
	Input,
	Toggle,
	Dropdown
};
use core\ui\windows\CustomForm;

use core\utils\TextFormat;

class EditShopItemUi extends CustomForm{

	public array $items = [];

	public function __construct(Player $player, public Shop $shop, public ShopItem $item, string $error = ""){
		parent::__construct("Edit shop item");
		$this->addElement(new Label(
			($error !== "" ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Edit this item's details below!")
		);

		$this->addElement(new Input("Price", 0, $item->getPrice()));
		
		$this->addElement(new Label("If you would like to change this shop item's position, select which item you'd like to swap it with!"));
		$items = ["No change"];
		foreach($shop->getShopItems() as $shopItem){
			if($shopItem !== $item){
				$this->items[] = $shopItem;
				$items[] = "Above " . $shopItem->getItem()->getName();
			}
		}
		$this->items[] = 1;
		$items[] = "Bottom";
		$dropdown = new Dropdown("Change position", $items);
		$dropdown->setOptionAsDefault("No change");
		$this->addElement($dropdown);
		
		$this->addElement(new Toggle(TextFormat::EMOJI_DENIED . " Delete"));
	}

	public function close(Player $player){
		/** @var SkyBlockPlayer $player */
		$island = $player->getGameSession()->getIslands()->getIslandAt();
		if($island === null){
			return;
		}
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if(!$perm->getPermission(Permissions::EDIT_SIGN_SHOPS)){
			return;
		}

		$shop = $this->shop->verify();
		if($shop === null){
			return;
		}

		$player->showModal(new ShopItemsUi($player, $shop));
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

		$item = $this->item;
		if(!$item->verify($shop)){
			$player->showModal(new ShopItemsUi($player, $shop, "This item is no longer available."));
			return;
		}

		if($response[4]){
			$player->showModal(new ConfirmDeleteShopItemUi($player, $shop, $item));
			return;
		}

		$price = (int) $response[1];
		if($price < 0){
			$player->showModal(new EditShopItemUi($player, $shop, $item, "Price must be at least 0 techits!"));
			return;
		}

		$moveItem = null;
		$bottom = false;

		$position = $response[3];
		if($position !== 0){
			$moveItem = $this->items[$position - 1];
			if(is_int($moveItem)){
				$bottom = true;
			}
		}

		$item->setPrice($price);
		if($moveItem !== null){
			$shop->removeShopItem($item);
			if($bottom){
				$shop->addShopItem($item, null, true);
			}else{
				$shop->addShopItem($item, $moveItem, true);
			}
		}

		$player->showModal(new ShopItemsUi($player, $shop, "Successfully edited shop item!", false));
	}

}
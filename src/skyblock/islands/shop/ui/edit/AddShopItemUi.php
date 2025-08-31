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
	Dropdown
};
use core\ui\windows\CustomForm;
use core\utils\conversion\LegacyItemIds;
use core\utils\TextFormat;

class AddShopItemUi extends CustomForm{

	public array $items = [];

	public array $placement = [];

	public function __construct(Player $player, public Shop $shop, string $error = ""){
		parent::__construct("Add shop item");
		$this->addElement(new Label(
			($error !== "" ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Select an item from the chest you'd like to start selling!"
		));

		$items = [];
		foreach($shop->getChest()->getInventory()->getContents() as $slot => $item){
			if(!$shop->itemInShop($item)){
				if(!isset($items[$key = LegacyItemIds::typeIdToLegacyId($item->getTypeId()) . ":" . LegacyItemIds::stateIdToMeta($item)])){
					$items[$key] = clone $item;
				}else{
					$items[$key]->setCount($items[$key]->getCount() + $item->getCount());
				}
			}
		}
		$this->items = array_values($items);
		$asText = [];
		foreach($items as $item) $asText[] = $item->getName() . " (x" . $item->getCount() . ")";
		$this->addElement(new Dropdown("Item", $asText));

		$this->addElement(new Input("Price", 0, 1));

		$this->addElement(new Label("Next, specify where you'd like this item placed in the shop"));
		$items = [];
		foreach($shop->getShopItems() as $shopItem){
			if($shopItem !== $item){
				$this->placement[] = $shopItem;
				$items[] = "Above " . $shopItem->getItem()->getName();
			}
		}
		$this->placement[] = 1;
		$items[] = "Bottom";
		$dropdown = new Dropdown("Change position", $items);
		$dropdown->setOptionAsDefault("Bottom");
		$this->addElement($dropdown);
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

		if(count($shop->getShopItems()) >= $shop->getMaxShopItems()){
			$player->showModal(new ShopItemsUi($player, $shop, "Max amount of items for sale reached!"));
			return;
		}

		if(count($this->items) === 0){
			$player->showModal(new AddShopItemUi($player, $shop, "No items in this chest available to add"));
			return;
		}

		$item = $this->items[$response[1]];
		if($shop->itemInShop($item)){
			$player->showModal(new AddShopItemUi($player, $shop, "This item is already for sale!"));
			return;
		}

		$price = (int) $response[2];
		if($price < 0 || $price >= 1000000000){
			$player->showModal(new AddShopItemUi($player, $shop, "Price must be within 0-1,000,000,000"));
			return;
		}

		$bottom = false;
		$position = $response[4];
		$moveItem = $this->placement[$position];
		if(is_int($moveItem)){
			$bottom = true;
		}

		$shopItem = new ShopItem(
			$shop,
			$item->setCount(1),
			$price
		);


		if($bottom){
			$shop->addShopItem($shopItem, null, true);
		}else{
			$shop->addShopItem($shopItem, $moveItem, true);
		}

		$player->showModal(new ShopItemsUi($player, $shop, "Successfully added shop item!", false));
	}

}
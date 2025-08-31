<?php namespace skyblock\islands\shop\ui\edit;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\islands\shop\{
	Shop,
	ShopItem
};

use core\ui\windows\ModalWindow;

use core\utils\TextFormat;

class ConfirmDeleteShopItemUi extends ModalWindow{

	public function __construct(Player $player, public Shop $shop, public ShopItem $shopItem){
		parent::__construct(
			"Delete " . $this->shopItem->getItem()->getName(),
			"Are you sure you would like to delete this shop item?",
			"Delete",
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
		if(!$perm->getPermission(Permissions::EDIT_SIGN_SHOPS)){
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
			$player->showModal(new ShopItemsUi($player, $shop, "This item is no longer available."));
			return;
		}
		if($response){
			$shop->removeShopItem($item);
			$player->showModal(new ShopItemsUi($player, $shop, "Successfully deleted " . $item->getItem()->getName() . TextFormat::RESET . TextFormat::GREEN . " from your store!", false));
		}else{
			$player->showModal(new EditShopItemUi($player, $shop, $item));
		}
	}

}
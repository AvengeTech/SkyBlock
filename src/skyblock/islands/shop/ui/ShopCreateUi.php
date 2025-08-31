<?php namespace skyblock\islands\shop\ui;

use pocketmine\block\tile\{
	Sign,
	Chest
};
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\block\WallSign;

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\conversion\LegacyItemIds;
use core\utils\TextFormat;

class ShopCreateUi extends SimpleForm{

	public array $items = [];

	public function __construct(public WallSign|Sign $sign){
		if($sign instanceof Sign) $this->sign = $sign = $sign->getPosition()->getWorld()->getBlock($sign->getPosition());

		/** @var WallSign $sign */
		$chest = $sign->getPosition()->getWorld()->getTile($sign->getSide(Facing::opposite($sign->getFacing()))->getPosition());
		if($chest === null || !$chest instanceof Chest){
			parent::__construct("error", "this sign is not connected to a chest!");
			return;
		}
		parent::__construct("Create sign shop", "Select the first item you'd like to sell in your new sign shop below!");
		$items = [];
		foreach($chest->getInventory()->getContents() as $slot => $item){
			if(!isset($items[($id = LegacyItemIds::typeIdToLegacyId($item->getTypeId()) . ":" . LegacyItemIds::stateIdToMeta($item))])){
				$items[$id] = clone $item;
			}else{
				$items[$id]->setCount($items[$id]->getCount() + $item->getCount());
			}
		}
		$this->items = array_values($items);
		foreach($items as $item){
			$this->addButton(new Button($item->getName() . TextFormat::RESET . PHP_EOL . "x" . $item->getCount()));
		}
	}
	
	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if(($pos = $this->sign->getPosition())->getWorld()->getTile($pos) === null){
			$player->sendMessage(TextFormat::RI . "Sign no longer exists!");
			return;
		}
		$chest = $this->sign->getPosition()->getWorld()->getTile($this->sign->getSide(Facing::opposite($this->sign->getFacing()))->getPosition());
		if($chest === null || !$chest instanceof Chest){
			$player->sendMessage(TextFormat::RI . "Sign is no longer connected to a chest!");
			return;
		}

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

		if(count(($sm = $island->getShopManager())->getShops()) > $sm->getShopLimit()){
			$player->sendMessage(TextFormat::RI . "This island has reached the max amount of sign shops for it's level!");
			return;
		}
		
		$item = $this->items[$response];
		$player->showModal(new FirstShopItemUi($player, $this->sign, $item));
	}

}
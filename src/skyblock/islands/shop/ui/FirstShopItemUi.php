<?php namespace skyblock\islands\shop\ui;

use pocketmine\block\WallSign;
use pocketmine\block\tile\{
	Chest
};
use pocketmine\item\Item;
use pocketmine\math\Facing;
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

class FirstShopItemUi extends CustomForm{
	
	public function __construct(Player $player, public WallSign $sign, public Item $firstItem, string $error = ""){
		/** @var SkyBlockPlayer $player */
		$this->firstItem = clone $this->firstItem;
		$this->firstItem->setCount(1);
		$chest = ($pos = $sign->getPosition())->getWorld()->getTile(($sb = $pos->getWorld()->getBlock($pos))->getSide(Facing::opposite($sign->getFacing()))->getPosition());
		if($chest === null || !$chest instanceof Chest){
			parent::__construct("Error", "This sign is no longer connected to a chest!");
			return;
		}
		$island = $player->getGameSession()->getIslands()->getIslandAt();
		if($island === null){
			$player->sendMessage(TextFormat::RI . "You are not at an island!");
			return;
		}
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		$hierarchy = $perm->getHierarchy();

		parent::__construct("Finish Shop Setup");
		$this->addElement(new Label(
			($error ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"How much would you like to sell x1 of this item for?" . PHP_EOL . PHP_EOL . "Item: " . $firstItem->getName())
		);
		$this->addElement(new Input("Price", 1));
		
		$this->addElement(new Label("Next, tell us a little more about your shop!"));
		$this->addElement(new Input("Name", "My New Shop"));
		$this->addElement(new Input("Description", "Welcome to my new shop!"));
		$this->addElement(new Input("Hierarchy Level", "1", $hierarchy));

		$this->addElement(new Label("Once you've filled out all of the information above, press 'Submit' to create your sign shop."));
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

		$item = $this->firstItem;

		$price = (int) $response[1];
		if($price < 0 || $price > 1000000000){
			$player->showModal(new FirstShopItemUi($player, $this->sign, $item, "Price must be within 0-1,000,000,000"));
			return;
		}

		$name = $response[3];
		if(strlen($name) > 16){
			$player->showModal(new FirstShopItemUi($player, $this->sign, $item, "Store name must be within 16 characters!"));
			return;
		}
		$description = $response[4];
		if(strlen($description) > 256){
			$player->showModal(new FirstShopItemUi($player, $this->sign, $item, "Store description must be within 256 characters!"));
			return;
		}

		$hierarchy = (int) $response[5];
		if($hierarchy > $perm->getHierarchy()){
			$player->showModal(new FirstShopItemUi($player, $this->sign, $item, "You cannot set this shop's hierarchy level higher than your own!"));
			return;
		}

		if(strlen($name) === 0){
			$player->showModal(new FirstShopItemUi($player, $this->sign, $item, "Name cannot be blank!"));
			return;
		}

		$sm = $island->getShopManager();
		$shop = new Shop($sm,
			time(),
			$name,
			$description,
			$hierarchy,
			$this->sign->getPosition()->asVector3()
		);
		$shop->addShopItem(new ShopItem(
			$shop,
			$item,
			$price
		), null, true);
		$sm->addShop($shop);
		$player->sendMessage(TextFormat::GI . "Successfully created sign shop!");
	}

}
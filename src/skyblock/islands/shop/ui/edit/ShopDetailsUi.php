<?php namespace skyblock\islands\shop\ui\edit;

use pocketmine\item\Item;
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

class ShopDetailsUi extends CustomForm{

	public function __construct(Player $player, public Shop $shop, string $error = ""){
		parent::__construct("Edit details");
		$this->addElement(new Label(
			($error !== "" ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Edit your shop's details below!")
		);
		$this->addElement(new Input("Name", "My New Shop", $shop->getName()));
		$this->addElement(new Input("Description", "Description", $shop->getDescription()));
		$this->addElement(new Input("Hierarchy Level", "1", $shop->getHierarchy()));
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

		$player->showModal(new EditShopUi($player, $shop));
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

		$name = $response[1];
		$description = $response[2];
		$hierarchy = (int) $response[3];

		if($hierarchy > $perm->getHierarchy()){
			$player->showModal(new ShopDetailsUi($player, $shop, "You cannot set this shop's hierarchy level higher than your own!"));
			return;
		}

		if(strlen($name) === 0){
			$player->showModal(new ShopDetailsUi($player, $shop, "Name cannot be blank!"));
			return;
		}

		$shop->setName($name);
		$shop->setDescription($description);
		$shop->setHierarchy($hierarchy);

		$player->showModal(new EditShopUi($player, $shop, "Successfully edited shop details!", false));
	}

}
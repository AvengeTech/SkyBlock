<?php namespace skyblock\islands\shop\ui\edit;

use pocketmine\block\tile\{
	Sign,
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

class ShopWithdrawUi extends CustomForm{

	public function __construct(Player $player, public Shop $shop, string $error = ""){
		parent::__construct("Withdraw");
		$this->addElement(new Label(($error !== "" ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "How many techits would you like to withdraw? Your bank has " . TextFormat::AQUA . number_format($shop->getBank()) . " techits"));
		$this->addElement(new Input("Amount", "0"));
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

		$amount = (int) $response[1];
		if($amount < 0){
			$player->showModal(new ShopWithdrawUi($player, $shop, "Amount must be at least 1 techit!"));
			return;
		}
		if($amount > $shop->getBank()){
			$player->showModal(new ShopWithdrawUi($player, $shop, "Your bank doesn't have that many techits!"));
			return;
		}

		$player->addTechits($amount);
		$shop->takeBank($amount);
		$player->showModal(new EditShopUi($player, $shop, "Successfully withdrew " . number_format($amount) . " techits from your bank!", false));
	}

}
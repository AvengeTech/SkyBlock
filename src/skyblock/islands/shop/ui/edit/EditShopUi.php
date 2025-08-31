<?php namespace skyblock\islands\shop\ui\edit;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\permission\Permissions;
use skyblock\islands\shop\Shop;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

use core\utils\TextFormat;

class EditShopUi extends SimpleForm{

	public array $items = [];

	public function __construct(Player $player, public Shop $shop, string $message = "", bool $error = true){
		parent::__construct(
			$shop->getName(),
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Shop name: " . $shop->getName() . TextFormat::RESET . TextFormat::WHITE . PHP_EOL .
			"Shop description: " . $shop->getDescription() . TextFormat::RESET . TextFormat::WHITE . PHP_EOL . PHP_EOL .

			"Bank balance: " . TextFormat::AQUA . number_format($shop->getBank()) . " techits" . TextFormat::WHITE . PHP_EOL . PHP_EOL .

			"Select an option below!"
		);

		$this->addButton(new Button("Withdraw from bank"));
		$this->addButton(new Button("Edit details"));
		$this->addButton(new Button("Edit items"));
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
			$player->showModal(new ShopWithdrawUi($player, $shop));
			return;
		}
		if($response === 1){
			$player->showModal(new ShopDetailsUi($player, $shop));
			return;
		}
		if($response === 2){
			$player->showModal(new ShopItemsUi($player, $shop));
			return;
		}
	}

}
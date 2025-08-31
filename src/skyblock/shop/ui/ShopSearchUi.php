<?php namespace skyblock\shop\ui;

use pocketmine\player\Player;

use skyblock\shop\ui\ErrorUi;
use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};

use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Input
};
use core\utils\TextFormat;

class ShopSearchUi extends CustomForm{

	public function __construct(){
		parent::__construct("Shop Search");

		$this->addElement(new Label("Enter the name of the item you are searching for!"));
		$this->addElement(new Input("Item Name", "dirt", ""));
	}

	public function close(Player $player){
		/** @var SkyBlockPlayer $player */
		$player->showModal(new ShopUi($player));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$name = strtolower($response[1]);
		if($name === ""){
			$player->showModal(new ErrorUi($this, "Name can't be empty!"));
			return;
		}

		$session = $player->getGameSession()->getIslands();
		$island = $session->atIsland() ? $session->getIslandAt() : $session->getLastIslandAt();
		if($island === null){
			$player->sendMessage(TextFormat::RI . "You are not at an island!");
			return;
		}
		$items = [];
		for($i = 1; $i <= min(16, $island->getSizeLevel()); $i++){
			$items = array_merge($items, SkyBlock::getInstance()->getShops()->getCategoryByLevel($i)->getItems());
		}

		if(isset($items[$name])){
			$item = $items[$name];
			$player->showModal(new ItemDealUi($item->getLevel(), $item, true));
			return;
		}
		$player->showModal(new ErrorUi($this, "Item with that name not found!"));
	}

}
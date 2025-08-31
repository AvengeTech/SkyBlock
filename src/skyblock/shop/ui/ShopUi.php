<?php namespace skyblock\shop\ui;

use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};
use skyblock\islands\ui\manage\IslandInfoUi;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

class ShopUi extends SimpleForm{

	public int $size;

	public function __construct(Player $player, public bool $back = false){
		parent::__construct("Shop", "Select an option below!");

		$this->addButton(new Button("Search"));

		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession()->getIslands();
		$island = $session->atIsland() ? $session->getIslandAt() : $session->getLastIslandAt();
		if($island !== null){
			for($i = 1; $i <= ($this->size = min(16, $island->getSizeLevel())); $i++){
				$this->addButton(SkyBlock::getInstance()->getShops()->getCategoryByLevel($i)->getButton());
			}
		}
		if($back){
			$this->addButton(new Button("Go back"));
		}
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession()->getIslands();
		if(!$session->atIsland() && $session->getLastIslandAt() === null){
			$player->sendMessage(TextFormat::RI . "You are not at an island!");
			return;
		}
		if($this->back && $response > $this->size){
			$player->showModal(new IslandInfoUi($player, ($session->getIslandAt() ?? $session->getLastIslandAt())));
			return;
		}
		if($response === 0){
			$player->showModal(new ShopSearchUi($player));
			return;
		}
		$player->showModal(new CategoryUi($response, $this->back));
	}
}
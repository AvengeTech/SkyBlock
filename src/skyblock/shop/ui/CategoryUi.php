<?php namespace skyblock\shop\ui;

use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

class CategoryUi extends SimpleForm{

	public array $items = [];

	public function __construct(public int $id, public bool $back){
		parent::__construct("Level " . $id, "Select an option below!");
		$this->items = array_values((SkyBlock::getInstance()->getShops()->getCategoryByLevel($id)?->getItems() ?? []));

		foreach($this->items as $item){
			$this->addButton($item->getButton());
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		foreach($this->items as $key => $item){
			if($key === $response){
				$player->showModal(new ItemDealUi($this->id, $item, $this->back));
				return;
			}
		}
		$player->showModal(new ShopUi($player, $this->back));
	}

	public function getId() : int{
		return $this->id;
	}

}
<?php namespace skyblock\enchantments\uis\blacksmith;

use pocketmine\player\Player;
use pocketmine\item\Durable;

use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Dropdown
};

use core\utils\TextFormat;
use skyblock\SkyBlockPlayer;

class RepairItemUi extends CustomForm{

	public $items = [];

	public function __construct(Player $player){
		parent::__construct("Repair Item");

		$this->addElement(new Label("What item would you like to repair?"));

		$dropdown = new Dropdown("Item selection");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if(($item instanceof Durable) && $item->getDamage() != 0){
				$this->items[$key] = $item;
				$dropdown->addOption($item->getName() . TextFormat::RESET . TextFormat::WHITE . " (" . $item->getDamage() . " uses)");
				$key++;
			}
		}
		$this->addElement($dropdown);

		$this->addElement(new Label("Press 'Submit' to calculate how much this repair is going to cost!"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if(empty($this->items)) return;

		$item = $this->items[$response[1]];
		$player->showModal(new ConfirmRepairUi($player, $item));
	}

}
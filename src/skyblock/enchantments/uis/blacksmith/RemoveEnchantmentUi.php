<?php namespace skyblock\enchantments\uis\blacksmith;

use pocketmine\item\Durable;
use pocketmine\player\Player;

use skyblock\enchantments\ItemData;
use skyblock\enchantments\item\EnchantmentRemover;

use core\ui\elements\customForm\{
	Label,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;
use skyblock\enchantments\item\UnboundTome;
use skyblock\SkyBlockPlayer;

class RemoveEnchantmentUi extends CustomForm{

	public array $items = [];
	public array $removers = [];

	public function __construct(Player $player){
		parent::__construct("Remove enchantment");

		$this->addElement(new Label("Which item would you like to remove an enchantment from?"));

		$dropdown = new Dropdown("Item selection");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item instanceof Durable && $item->hasEnchantments()){
				$this->items[$key] = $item;
				$dropdown->addOption($item->getName() . TextFormat::RESET . TextFormat::WHITE . " (" . count($item->getEnchantments()) . " enchantments)");
				$key++;
			}
		}
		$this->addElement($dropdown);

		$this->addElement(new Label("Which enchantment remover would you like to use?"));
		$dropdown = new Dropdown("Enchantment remover");
		$key = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item instanceof UnboundTome){
				$this->removers[$key] = $item;
				$dropdown->addOption("Return: " . $item->getReturnChance() . "%% - XP: " . $item->getCost());
				$key++;
			}
		}
		$this->addElement($dropdown);
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if(count($this->items) < 1) return;
		if(count($this->removers) < 1) return;

		$item = $this->items[$response[1]];
		$slot = $player->getInventory()->first($item, true);
		if($slot === -1){
			$player->sendMessage(TextFormat::RI . "This tool is no longer in your inventory!");
			return;
		}
		
		$remover = $this->removers[$response[3]];
		$slot = $player->getInventory()->first($remover, true);
		if($slot === -1){
			$player->sendMessage(TextFormat::RI . "This enchantment remover is no longer in your inventory!");
			return;
		}
		if($remover->getCost() > $player->getXpManager()->getXpLevel()){
			$player->sendMessage(TextFormat::RI . "You don't have enough XP levels to use this enchantment remover!");
			return;
		}

		$player->showModal(new SelectEnchantmentUi($item, $remover));
	}

}
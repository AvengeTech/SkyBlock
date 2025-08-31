<?php namespace skyblock\enchantments\uis\blacksmith;

use pocketmine\{
	player\Player,
	Server
};
use pocketmine\item\Item;

use skyblock\enchantments\event\RepairItemEvent;

use core\ui\windows\ModalWindow;

use core\utils\TextFormat;
use pocketmine\item\Durable;
use skyblock\SkyBlockPlayer;

class ConfirmRepairUi extends ModalWindow{

	public int $price;

	public function __construct(Player $player, public Item $item){
		/** @var Durable $item */
		/** @var SkyBlockPlayer $player */
		$price = ceil(min(30, $item->getDamage() / 25) + (count($item->getEnchantments()) * 2));
		$discount = match($player->getRank()){
			"endermite" => 5,
			"blaze" => 10,
			"ghast" => 20,
			"enderman" => 25,
			"wither" => 30,
			"youtuber" => 50,
			"enderdragon" => 50,
			"trainee" => 50,
			"mod" => 50,
			"owner" => 50,
			default => 0,
		};
		$this->price = (int) ($price - ($price * ($discount / 100)));
		parent::__construct("Confirm Repair", "Repairing this item will cost " . TextFormat::YELLOW . $this->price . " XP Levels" . ($discount > 0 ? TextFormat::AQUA . " (" . $discount . " percent off with " . $player->getRank() . " rank!)" : "") . TextFormat::WHITE . ", are you sure you want to repair this item?", "Repair item", "Go back");
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if($response){
			$item = $player->getInventory()->first($this->item, true);
			if($item == -1){
				$player->sendMessage(TextFormat::RN . "Item you're trying to repair no longer exists in inventory!");
				return;
			}

			if($player->getXpManager()->getXpLevel() < $this->price){
				$player->sendMessage(TextFormat::RN . "You do not have enough XP Levels to repair this!");
				return;
			}

			$slot = $item;
			/** @var Durable $item */
			$item = $player->getInventory()->getItem($slot);

			$ev = new RepairItemEvent($item, $player, $this->price);
			$ev->call();

			$item->setDamage(0);
			$player->getInventory()->setItem($slot, $item);

			$player->getXpManager()->subtractXpLevels($this->price);
			$player->sendMessage(TextFormat::GI . "Successfully repaired this item!");
		}else{
			$player->showModal(new RepairItemUi($player));
		}
	}

}
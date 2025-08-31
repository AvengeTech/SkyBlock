<?php namespace skyblock\enchantments\uis\enchanter;

use pocketmine\item\Item;
use pocketmine\player\Player;

use skyblock\enchantments\ItemData;
use skyblock\enchantments\item\EnchantmentBook;

use core\ui\windows\ModalWindow;
use core\utils\TextFormat;
use pocketmine\world\sound\AnvilBreakSound;
use pocketmine\world\sound\AnvilUseSound;
use skyblock\enchantments\event\ApplyEnchantmentEvent;
use skyblock\SkyBlockPlayer;

class EnchantConfirmUi extends ModalWindow{

	public function __construct(
		public Item $item,
		public EnchantmentBook $book
	){
		parent::__construct(
			"Confirm Enchant",
			"It will cost you " . TextFormat::YELLOW . $book->getApplyCost() . " XP Levels " . TextFormat::WHITE . "to apply this enchantment. Are you sure you want to apply this enchantment to this item?",
			"Apply Enchantment",
			"Cancel"
		);
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if($response){
			$item = $this->item;
			$book = $this->book;
			$slot1 = $player->getInventory()->first($item, true);
			$slot2 = $player->getInventory()->first($book, true);

			if($slot1 == -1 || $slot2 == -1){
				$player->sendMessage(TextFormat::RN . "One of the items you're trying to use is no longer in your inventory.");
				return;
			}
			if($player->getXpManager()->getXpLevel() < $book->getApplyCost()){
				$player->sendMessage(TextFormat::RN . "You do not have enough XP to enchant this item!");
				return;
			}

			if (mt_rand(1, 100) <= $book->getApplyChance()) {
				$data = new ItemData($player->getInventory()->getItem($slot1));
				$data->addEnchantment($book->getEnchant(), $book->getEnchant()->getStoredLevel());
				$player->getInventory()->setItem($slot1, $data->getItem());
				$book->pop();
				$player->getInventory()->setItem($slot2, $book);

				$player->getXpManager()->subtractXpLevels($book->getApplyCost());

				$ev = new ApplyEnchantmentEvent($book->getEnchant(), $item, $player);
				$ev->call();

				$player->sendMessage(TextFormat::GI . "Successfully enchanted your item!");
				$player->broadcastSound(new AnvilUseSound, [$player]);
			} else {
				$book->pop();
				$player->getInventory()->setItem($slot2, $book);

				$player->sendMessage(TextFormat::RI . "Oh no! Failed to enchant your item!");
				$player->broadcastSound(new AnvilBreakSound, [$player]);
			}
		}else{
			$player->showModal(new SelectItemUi($player));
		}
	}

}
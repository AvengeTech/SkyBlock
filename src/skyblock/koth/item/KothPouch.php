<?php

namespace skyblock\koth\item;

use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use skyblock\SkyBlockPlayer;

use skyblock\enchantments\EnchantmentData as ED;
use skyblock\enchantments\item\MaxBook;

class KothPouch extends Item{

	public const TAG_INIT = "init";

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);

		$this->setCustomName(TF::LIGHT_PURPLE . "Koth Pouch");
		$lores = [];
		$lores[] = TF::GRAY . "Pouch full of koth rewards.";
		$lores[] = TF::GRAY . " ";
		$lores[] = TF::GRAY . "Tap/Right-Click to use this item.";
		foreach($lores as $key => $lore) $lores[$key] = TF::RESET . $lore;

		$this->setLore($lores);

		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([]));
		return $this;
	}

	/** @param SkyBlockPlayer $player */
	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		$gs = $player->getGameSession();
		$winnerRewardTexts = [];

		$player->addTechits(25000);
		$winnerRewardTexts[] = "25,000 techits";
		$gs->getCrates()->addKeys(($kt = (mt_rand(1, 30) === 30 ? "emerald" : "diamond")), $cnt = (round(lcg_value() * 100, 2) <= 80 ? mt_rand(4, 6) : mt_rand(7, 9)));
		$winnerRewardTexts[] = $cnt . " $kt keys";

		if(round(lcg_value() * 100, 2) <= 27.35){
			$count = mt_rand(1, 5);
			$player->getInventory()->addItem(ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount($count));
			$winnerRewardTexts[] = $count . " Enchanted Golden Apples";
		}
		if (round(lcg_value() * 100, 2) <= 67.5) {
			$rarityType = floor(mt_rand(100, 515) / 100);

			$count = match ($rarityType) {
				ED::RARITY_COMMON => 5,
				ED::RARITY_UNCOMMON => 4,
				ED::RARITY_RARE => 3,
				ED::RARITY_LEGENDARY => 2,
				ED::RARITY_DIVINE => 1,
				default => 1
			};

			$book = ItemRegistry::MAX_BOOK();
			$book->setup(MaxBook::TYPE_MAX_RARITY, $rarityType, mt_rand(1, 4) == 4 ? ED::CAT_SWORD : ED::CAT_ARMOR, false);
			$book->setCount($count);
		} else {
			$book = ItemRegistry::MAX_BOOK();
			$book->setup(MaxBook::TYPE_MAX_RANDOM_RARITY, -1, mt_rand(1, 4) == 4 ? ED::CAT_SWORD : ED::CAT_ARMOR, true);
			$book->setCount(3);
		}

		if ($player->getInventory()->canAddItem($book)) {
			$book->init();
			$player->getInventory()->addItem($book);
			$winnerRewardTexts[] = $book->getCount() . " " . $book->getName();
		}
		if(round(lcg_value() * 100, 2) <= 15){
			$item = ItemRegistry::POUCH_OF_ESSENCE()->setup("Koth", (mt_rand(1, 5) * 250))->init();

			if($player->getInventory()->canAddItem($item)){
				$player->getInventory()->addItem($item);
			}
				
		}
		if (round(lcg_value() * 100, 2) <= 0.75) {
			$player->getGameSession()->getCrates()->addKeys("divine", 1);
			$winnerRewardTexts[] = "1 divine key";
		}
		if(round(lcg_value() * 100, 2) <= 3.75){
			$item = ItemRegistry::PET_KEY()->init();

			if($player->getInventory()->canAddItem($item)){
				$player->getInventory()->addItem($item);
			}
				
		}

		$this->pop();

		$textList = implode(PHP_EOL . TF::GRAY . "- " . TF::AQUA, $winnerRewardTexts);

		$player->sendMessage(TF::GI . "You received the following koth rewards: \n" . TF::GRAY . "- " . TF::AQUA . $textList);

		return ItemUseResult::SUCCESS();
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		if($tag->getByte(self::TAG_INIT, 0) === 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));
	}
}
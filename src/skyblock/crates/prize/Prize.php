<?php namespace skyblock\crates\prize;

use core\utils\ItemRegistry;
use pocketmine\item\{
	Item,
	ItemFactory,
};
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\crates\CrateData;
use skyblock\crates\commands\KeyPack;

use core\utils\TextFormat;
use skyblock\crates\filter\FilterSetting;
use skyblock\enchantments\effects\items\EffectItem;
use skyblock\enchantments\EnchantmentData;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\item\EnchantmentBook;
use skyblock\enchantments\item\MaxBook;
use skyblock\SkyBlockPlayer;

class Prize{

	const RARITY_TAGS = [
		CrateData::RARITY_COMMON => TextFormat::GREEN . "COMMON",
		CrateData::RARITY_UNCOMMON => TextFormat::DARK_GREEN . "UNCOMMON",
		CrateData::RARITY_RARE => TextFormat::YELLOW . "RARE",
		CrateData::RARITY_LEGENDARY => TextFormat::GOLD . "LEGENDARY",
		CrateData::RARITY_DIVINE => TextFormat::RED . "DIVINE",
	];

	public function __construct(
		public Item|PrizeVar $prize,
		public int $rarity,
		public int $filterType = FilterSetting::FILTER_NONE
	){}

	public function getPrize() : Item|PrizeVar{
		return $this->prize;
	}

	public function getRarity() : int{
		return $this->rarity;
	}

	public function getRarityTag() : string{
		return self::RARITY_TAGS[$this->getRarity()] ?? "";
	}

	public function getFilterCategory(): int {
		return $this->filterType;
	}

	public function getName() : string{
		$prize = $this->getPrize();
		$string = TextFormat::BOLD . $this->getRarityTag() . TextFormat::RESET . " " . TextFormat::AQUA;
		if($prize instanceof Item){
			$string .= "x" . $prize->getCount() . " " . TextFormat::clean($prize->getName());
		}
		if($prize instanceof EffectItem){
			$text = TextFormat::AQUA . "x" . $prize->getCount() . " ";
			switch($prize->getRarity()){
				case 1:
					return $text . "Common Animator";
				case 2:
					return $text . "Uncommon Animator";
				case 3:
					return $text . "Rare Animator";
				case 4:
					return $text . "Legendary Animator";
				case 5:
					return $text . "Divine Animator";
			}
		}
		if($prize instanceof PrizeVar){
			$key = $prize->getKey();
			$count = $prize->getCount();
			$extra = $prize->getExtra();

			switch($prize->getKey()){
				case "ck":
					$string .= "x" . $count . " " . ucfirst($extra[0]) . " Keys";
					break;
				case "kp":
					$string .= ucwords(str_replace("-", " ", $extra[0])) . " Key Pack";
					break;
				case "t":
					$string .= $count . " Techits";
					break;
				case "tag":
					$string .= $extra[0] . " Tag";
					break;
			}
		}
		return $string;
	}

	public function give(Player $player, bool $title = true, bool $skipFilter = false): void {
		/** @var SkyBlockPlayer $player */
		$prize = $this->getPrize();
		$session = $player->getGameSession()->getCrates();

		if (
			!$skipFilter &&
			$session->getFilter()->isEnabled() &&
			$this->getFilterCategory() !== FilterSetting::FILTER_NONE &&
			$session->getFilter()->getSetting($this->getFilterCategory())->getValue() &&
			!$session->getFilter()->isFull($player->getRank())
		) {
			if ($title) $player->sendTitle($this->getRarityTag(), TextFormat::YELLOW . "(Filtered) " . TextFormat::AQUA . $this->getName(), 10, 30, 10);

			$shopItem = ($prize instanceof Item ? SkyBlock::getInstance()->getShops()->getShopItem($prize) : null);
			$shopPrice = (is_null($shopItem) ? 0 : $shopItem->getSellPrice());

			$session->getFilter(true)->increaseCount(1);

			if ($session->getFilter()->isAutoClearing()) {
				$player->getGameSession()->getTechits()->addTechits($shopPrice);
			} else {
				$session->getFilter(true)->addInventoryValue($shopPrice);
			}

			return;
		}

		if ($title) $player->sendTitle($this->getRarityTag(), TextFormat::AQUA . $this->getName(), 10, 30, 10);

		if($prize instanceof Item){
			$this->prize = $prize; // Ensure the prize is updated in case it was modified
			$player->getInventory()->addItem($prize);
			return;
		}
		if($prize instanceof PrizeVar){
			$key = $prize->getKey();
			$count = $prize->getCount();
			$extra = $prize->getExtra();

			switch($key){
				case "ck":
					$player->getGameSession()->getCrates()->addKeys($extra[0], $count);
					break;
				case "kp":
					foreach((KeyPack::PACKS[$extra[0]] ?? []) as $type => $count){
						$player->getGameSession()->getCrates()->addKeys($type, $count);
					}
					break;
				case "t":
					$player->addTechits($count);
					break;
				case "tag":
					$tags = SkyBlock::getInstance()->getTags();
					$session = $player->getGameSession()->getTags();

					$tag = $tags->getTag($extra[0]);
					if($tag != null){
						if(!$session->hasTag($tag)){
							$session->addTag($tag);
						}else{
							$player->addTechits(2500);
							$player->sendMessage(TextFormat::RI . "You already have this tag, so you were given " . TextFormat::AQUA . "2,500 techits" . TextFormat::GRAY . " instead!");
						}
					}
					break;
				default:

					break;
			}
		}
	}

	public function __clone(){
		$this->prize = clone $this->prize;
	}

}
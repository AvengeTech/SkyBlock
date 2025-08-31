<?php namespace skyblock\fishing\object;

use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use core\utils\TextFormat as TF;
use pocketmine\item\VanillaItems;
use skyblock\crates\Crates;
use skyblock\enchantments\EnchantmentData;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\item\EnchantmentBook;
use skyblock\fishing\Structure;
use skyblock\generators\item\Extender;
use skyblock\generators\item\Solidifier;
use skyblock\pets\item\PetKey;
use skyblock\settings\SkyBlockSettings;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class FishingFind{

	public function __construct(
		private string $find,
		private array $data
	){}

	public function getFindType() : string{
		return explode(":", $this->find)[0];
	}

	public function getFindName() : string{
		return explode(":", $this->find)[1];
	}

	public function getCategory() : int{
		return $this->data[Structure::DATA_CATEGORY] ?? Structure::CATEGORY_JUNK;
	}

	public function getRarity() : int{
		return $this->data[Structure::DATA_RARITY] ?? Structure::RARITY_COMMON;
	}

	public function getPercent() : int|float{
		return $this->data[Structure::DATA_PERCENT] ?? -1;
	}

	public function isWaterExclusive() : bool{
		return $this->data[Structure::DATA_WATER_EXCLUSIVE] ?? false;
	}

	public function isLavaExclusive() : bool{
		return $this->data[Structure::DATA_LAVA_EXCLUSIVE] ?? false;
	}

	public function isNonExclusive() : bool{
		return !$this->isLavaExclusive() && !$this->isWaterExclusive();
	}

	public function getExtraData() : array{
		return $this->data[Structure::DATA_EXTRA] ?? [];
	}

	public function getBrokenChance() : int|float{
		if(empty($this->getExtraData())) return -1;

		return (!isset($this->getExtraData()[Structure::EXTRA_BROKEN_CHANCE]) ? -1 : $this->getExtraData()[Structure::EXTRA_BROKEN_CHANCE]);
	}

	public function getMinDamage() : int|float{
		if(empty($this->getExtraData())) return -1;

		return (!isset($this->getExtraData()[Structure::EXTRA_MIN_DAMAGE]) ? -1 : $this->getExtraData()[Structure::EXTRA_MIN_DAMAGE]);
	}

	public function getChanceForMax() : int|float{
		if(empty($this->getExtraData())) return -1;

		return (!isset($this->getExtraData()[Structure::EXTRA_CHANCE_FOR_MAX]) ? -1 : $this->getExtraData()[Structure::EXTRA_CHANCE_FOR_MAX]);
	}

	public function getLevelChances() : array{
		if(empty($this->getExtraData())) return [];

		return (!isset($this->getExtraData()[Structure::EXTRA_LEVEL_CHANCES]) ? -1 : $this->getExtraData()[Structure::EXTRA_LEVEL_CHANCES]);
	}

	public function getWaterChance() : int|float{
		if(empty($this->getExtraData())) return -1;

		return (!isset($this->getExtraData()[Structure::EXTRA_WATER_CHANCE]) ? -1 : $this->getExtraData()[Structure::EXTRA_WATER_CHANCE]);
	}

	public function getLavaChance() : int|float{
		if(empty($this->getExtraData())) return -1;

		return (!isset($this->getExtraData()[Structure::EXTRA_LAVA_CHANCE]) ? -1 : $this->getExtraData()[Structure::EXTRA_LAVA_CHANCE]);
	}

	public function getItem() : ?Item{
		$item = StringToItemParser::getInstance()->parse($this->getFindName());

		if(is_null($item)){
			return VanillaItems::PAPER()->setCustomName(TF::RED . "Invalid Item")
				->setLore([
					TF::GRAY . "The item " . TF::YELLOW . $this->getFindName() . TF::GRAY . " is invalid.",
					TF::GRAY . "Please report this to the staff team."
				]);
			// return null;
		}

		return $item;
	}

	public function getXpDrops() : int{
		switch($this->getCategory()){
			case Structure::CATEGORY_FISH:
				return match($this->getRarity()){
					Structure::RARITY_COMMON => mt_rand(0, 2),
					Structure::RARITY_UNCOMMON => mt_rand(2, 3),
					Structure::RARITY_RARE => mt_rand(3, 4),
					Structure::RARITY_LEGENDARY => mt_rand(4, 8),
					Structure::RARITY_DIVINE => mt_rand(8, 10)
				};
			case Structure::CATEGORY_JUNK:
				return match($this->getRarity()){
					Structure::RARITY_COMMON => 0,
					Structure::RARITY_UNCOMMON => 0,
					Structure::RARITY_RARE => mt_rand(0, 3),
					Structure::RARITY_LEGENDARY => mt_rand(0, 5),
					Structure::RARITY_DIVINE => mt_rand(0, 8)
				};
			case Structure::CATEGORY_TREASURE:
				return match($this->getRarity()){
					Structure::RARITY_COMMON => mt_rand(0, 2),
					Structure::RARITY_UNCOMMON => mt_rand(2, 3),
					Structure::RARITY_RARE => mt_rand(3, 4),
					Structure::RARITY_LEGENDARY => mt_rand(4, 8),
					Structure::RARITY_DIVINE => mt_rand(8, 10)
				};
			case Structure::CATEGORY_RESOURCE:
				return match($this->getRarity()){
					Structure::RARITY_COMMON => mt_rand(0, 2),
					Structure::RARITY_UNCOMMON => mt_rand(2, 3),
					Structure::RARITY_RARE => mt_rand(3, 4),
					Structure::RARITY_LEGENDARY => mt_rand(4, 8),
					Structure::RARITY_DIVINE => mt_rand(8, 10)
				};
		}

		return 0;
	}

	public function give(SkyBlockPlayer $player, bool $giveXp = true, int $xpMultiplier = 1) : void{
		switch($this->getFindType()){
			case Structure::TYPE_ITEM:
				$item = StringToItemParser::getInstance()->parse($this->getFindName());

				if(is_null($item)){
					echo "Fishing Find: " . $this->getFindName() . " is null\n";
					return;
				}

				if($item instanceof Durable){
					if(is_int($this->getBrokenChance()) && $this->getBrokenChance() === -1 || round(lcg_value() * 100, 5) <= $this->getBrokenChance()){
						if($this->getMinDamage() !== -1){
							$min = (int) (round($this->getMinDamage(), 2) / $item->getMaxDurability()) * 100;

							$damage = mt_rand($min, $item->getMaxDurability());
						}else{
							$damage = mt_rand(10, $item->getMaxDurability() - 1);
						}

						$item->setDamage($damage);
					}
				}

				if($item instanceof EnchantmentBook){
					$enchantment = EnchantmentRegistry::getRandomEnchantment($item->getRarity(), EnchantmentData::CAT_FISHING_ROD);

					if($this->getChanceForMax() == -1){
						$enchantment->setStoredLevel(mt_rand(1, max(1, $enchantment->getMaxLevel() - 1)));
					}else{
						if($enchantment->getMaxLevel() === 1) $enchantment->setStoredLevel(1);

						if(round(lcg_value() * 100, 5) <= $this->getChanceForMax() || $enchantment->getMaxLevel() == 1){
							$enchantment->setStoredLevel($enchantment->getMaxLevel());
						}else{
							$enchantment->setStoredLevel(mt_rand(1, max(1, $enchantment->getMaxLevel() - 1)));
						}
					}

					$item->setup($enchantment);
					$item->setEnchantmentCategory(EnchantmentData::CAT_FISHING_ROD);
				}elseif($item instanceof Extender){
					$chances = $this->getLevelChances();

					if(round(lcg_value() * 100, 5) <= $chances[2]){
						$item->setup(2);
					}else{
						$item->setup(1);
					}

					$item->init();
				}elseif($item instanceof Solidifier){
					$chances = $this->getLevelChances();
					$chance = round(lcg_value() * 100, 5);

					$level = match(true){
						($chance <= $chances[1]) => 1,
						($chance <= $chances[2]) => 2,
						($chance <= $chances[3]) => 3,
						($chance <= $chances[4]) => 4,
						($chance <= $chances[5]) => 5
					};

					$item->setup($level, 500)->init();
				}elseif($item instanceof PetKey){
					$item->init();
				}

				$setting = $player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::AUTO_INV);

				if($setting){
					$player->getInventory()->addItem($item);
				}else{
					$player->getWorld()->dropItem($player->getPosition(), $item);
				}
				break;

			case Structure::TYPE_KEY:
				$player->getGameSession()->getCrates()->addKeys($this->getFindName());

				$player->sendTitle(TF::YELLOW . Crates::FIND_WORDS[array_rand(Crates::FIND_WORDS)], TF::YELLOW . "Found x1 " . Crates::KEY_COLORS[$this->getFindName()] . ucfirst($this->getFindName()) . " Key", 10, 40, 10);
				break;
		}

		if($giveXp) $player->getXpManager()->addXp(floor($this->getXpDrops() * $xpMultiplier));
	}

}
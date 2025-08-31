<?php

namespace skyblock\fishing;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use skyblock\SkyBlock;
use skyblock\fishing\{
	entity\Hook,
	object\FishingFind
};

class Fishing{

	/** @var FishingFind[] $finds */
	private array $finds = [];

	public function __construct(public SkyBlock $plugin) {
		EntityFactory::getInstance()->register(Hook::class, function (World $world, CompoundTag $nbt): Hook {
			return new Hook(EntityDataHelper::parseLocation($nbt, $world), null);
		}, ["Hook", "minecraft:fishing_hook"]);

		$this->setupFinds();
	}

	public function setupFinds() : void{
		foreach(Structure::FINDS as $name => $data){
			$this->finds[$name] = new FishingFind($name, $data);
		}
	}

	public function getRandomFind(bool $isLava = false, array $categories = [], float $multiplier = 1.0, ?FishingComponent $session = null) : FishingFind {
		if(empty($categories)){
			$finds = $this->getLiquidLootPool(($isLava ? Hook::LIQUID_LAVA : Hook::LIQUID_WATER));
		}else{
			$finds = $this->getFindsByCategories($categories, $this->getLiquidLootPool(($isLava ? Hook::LIQUID_LAVA : Hook::LIQUID_WATER)));
		}

		if(!is_null($session)){
			if(
				$isLava && $session->lastTimeLavaTreasure >= 10 ||
				!$isLava && $session->lastTimeWaterTreasure >= 20
			){
				$finds = $this->getFindsByCategories([Structure::CATEGORY_TREASURE], $this->getLiquidLootPool(($isLava ? Hook::LIQUID_LAVA : Hook::LIQUID_WATER)));
			}
		}

		if(empty($finds)) return $this->finds[($isLava ? "i:cooked_cod" : "i:raw_cod")];

		$multiplier = max(1.0, $multiplier);
		$attempts = 250;
		$found = null;

		while(is_null($found)){
			if($attempts <= 0) break;

			$find = $finds[array_rand($finds)];

			if(!$find->isNonExclusive() && ($isLava && $find->isWaterExclusive() || !$isLava && $find->isLavaExclusive())){
				$attempts--;
				continue;
			}

			if(is_int($find->getPercent())){
				switch($find->getPercent()){
					case -1:
						$found = $find;
						continue 2;
						break;
					
					case -2:
						if(!(
							$isLava && round(lcg_value() * 100, 5) <= ($find->getLavaChance() * $multiplier) ||
							!$isLava && round(lcg_value() * 100, 5) <= ($find->getWaterChance() * $multiplier)
						)){
							$attempts--;
							continue 2;
						}

						$found = $find;
						continue 2;
						break;
				}
			}

			if(!(round(lcg_value() * 100, 5) <= ($find->getPercent() * $multiplier))){
				$attempts--;
				continue;
			}

			$found = $find;
		}
		
		if($isLava && $session->lastTimeLavaTreasure >= 10){
			$session->lastTimeLavaTreasure = 0;
		}elseif(!$isLava && $session->lastTimeWaterTreasure >= 20){
			$session->lastTimeWaterTreasure = 0;
		}

		($isLava ? $session->lastTimeLavaTreasure++ : $session->lastTimeWaterTreasure++);

		/** @var FishingFind|null $found */
		if(!is_null($found)){
			if($isLava && $found->getCategory() == Structure::CATEGORY_TREASURE) $session->lastTimeLavaTreasure = 0;
			if(!$isLava && $found->getCategory() == Structure::CATEGORY_TREASURE) $session->lastTimeWaterTreasure = 0;

			return $found;
		}

		$finds = (in_array(Structure::CATEGORY_TREASURE, $categories) ? [$this->finds["i:golden_apple"]] : ($isLava ? [
			$this->finds["i:cooked_cod"],
			$this->finds["i:cooked_salmon"],
		] : [
			$this->finds["i:raw_cod"],
			$this->finds["i:raw_salmon"],
		]));

		return $finds[array_rand($finds)];
	}

	/** @return FishingFind[] */
	public function getFindsByCategories(array $categories, array $pool = []) : array{
		if(empty($pool)) $pool = $this->finds;

		$finds = [];

		foreach($pool as $find){
			if(in_array($find->getCategory(), $categories)) $finds[] = $find;
		}

		return $finds;
	}

	/** @return FishingFind[] */
	public function getLiquidLootPool(int $liquid) : array{
		$loot = [];

		foreach($this->finds as $find){
			if(
				$liquid === Hook::LIQUID_LAVA && $find->isLavaExclusive() ||
				$liquid === Hook::LIQUID_WATER && $find->isWaterExclusive() ||
				$find->isNonExclusive()
			) $loot[] = $find;
		}

		return $loot;
	}
}

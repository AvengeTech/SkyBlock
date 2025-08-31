<?php namespace skyblock\shop;

use core\block\tile\Chest;
use core\utils\TextFormat;
use pocketmine\block\MobHead;
use pocketmine\item\{
	Item,
	StringToItemParser
};
use pocketmine\player\Player;

use skyblock\shop\data\{
	ShopCategory,
	ShopItem
};
use skyblock\shop\commands\{
	ShopCommand,
	SellHandCommand,
	SellInventoryCommand,
	SellChestCommand
};
use skyblock\shop\event\ShopSellEvent;
use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};
use skyblock\farming\Structure as FarmingStructure;
use skyblock\pets\Structure;

class Shops{

	/** @var ShopCategory[] $categories */
	private array $categories = [];
	/** @var ShopItem[] $keyCache */
	private array $keyCache = [];
	private array $chestmode = [];

	public function __construct(public SkyBlock $plugin){
		$plugin->getServer()->getCommandMap()->registerAll("shop", [
			new ShopCommand($plugin, "shop", "Open shop menu"),
			new SellHandCommand($plugin, "sellhand", "Sell the item you're holding"),
			new SellInventoryCommand($plugin, "sellinventory", "Sell all items in your inventory available for sale. (Ranked)"),
			new SellChestCommand($plugin, "sellchest", "Enable sell chest mode. (Ranked)")
		]);

		foreach(ShopPrices::SHOP_ITEMS as $level => $data){
			$this->categories[$level] = $category = new ShopCategory($level);

			foreach($category->getItems() as $key => $item){
				$this->keyCache[$key] = $item;
			}
		}
	}

	public function getCategories() : array{ return $this->categories; }

	public function getCategoryByLevel(int $level) : ?ShopCategory{ return $this->categories[$level] ?? null; }

	public function getShopItem(Item $item) : ?ShopItem{
		foreach(StringToItemParser::getInstance()->lookupAliases($item) as $alias){
			if(isset($this->keyCache[$alias])) return $this->keyCache[$alias];
		}

		return null;
	}

	public function getValue(Item $item, int $islandLevel = -1, ?SkyBlockPlayer $player = null) : float{
		$shopitem = $this->getShopItem($item);

		if(is_null($shopitem)) return -1;

		if($islandLevel !== -1 && $shopitem->getLevel() <= $islandLevel){
			$price = $shopitem->getSellPrice() * $item->getCount();

			// PET BUFF
			if(!is_null($player)){
				$session = $player->getGameSession()->getPets();
				$pet = $session->getActivePet();

				if(!is_null($pet)){
					$petData = $pet->getPetData();
					$buffData = array_values($petData->getBuffData());

					switch($petData->getIdentifier()){
						case Structure::RABBIT:
							foreach(StringToItemParser::getInstance()->lookupAliases($item) as $alias){
								if(
									FarmingStructure::getLevel($alias) !== -1
								){
									$price *= $buffData[0];
									break;
								}
							}
							break;

						case Structure::VEX:
							$mob_drops = [ // does not include crops
								'raw_porkchop', 'raw_chicken', 'raw_mutton', 'raw_beef',
								'cooked_porkchop', 'cooked_chicken', 'cooked_mutton', 'cooked_beef',
								'feather', 'leather', 'white_wool', 'string', 'spider_eye', 'arrow',
								'bone', 'skeleton_skull', 'rotten_flesh', 'zombie_head', 'gun_powder',
								'creeper_head', 'disc_fragment_5', 'red_mushroom', 'brown_mushroom',
								'white_mushroom', 'wither_rose', 'withered_bone', 'wither_skeleton_skull',
								'blaze_rod', 'breeze_rod', 'ender_pearl', 'eye_of_ender', 'jewel_of_the_end',
								'glowstone_dust', 'sugar', 'iron_ingot', 'iron_block'
							];

							foreach(StringToItemParser::getInstance()->lookupAliases($item) as $alias){
								if(!in_array($alias, $mob_drops)) continue;

								if(count($buffData) > 1){
									$price *= $buffData[1];
									break;
								}
							}
							break;
					}
				}
			}

			return $price;
		}

		return -1;
	}

	public function sellHand(Player $player) : float{
		/** @var SkyBlockPlayer $player */
		$isession = $player->getGameSession()->getIslands();
		$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();

		if(is_null($island)) return -1;

		$hand = $player->getInventory()->getItemInHand();
		$level = $island->getSizeLevel();
		$price = $this->getValue($hand, $level, $player);

		if($price > -1){
			if(is_null(($shopitem = $this->getShopItem($hand)))) return -1;

			$player->addTechits($price);
			$player->getInventory()->clear($player->getInventory()->getHeldItemIndex());

			$ev = new ShopSellEvent($shopitem, $hand->getCount(), $player);
			$ev->call();
		}

		return $price;
	}

	public function sellInventory(Player $player) : array{
		/** @var SkyBlockPlayer $player */
		$isession = $player->getGameSession()->getIslands();
		$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();
		$array = [
			"count" => 0,
			"price" => 0,
		];

		if(is_null($island)) return $array;

		$level = $island->getSizeLevel();

		foreach($player->getInventory()->getContents() as $slot => $item){
			$price = $this->getValue($item, $level, $player);

			if($price > -1){
				$array["price"] += $price;
				$array["count"] += $item->getCount();

				if(is_null(($shopitem = $this->getShopItem($item)))) continue;

				$player->addTechits($price);
				$player->getInventory()->clear($slot);
	
				$ev = new ShopSellEvent($shopitem, $item->getCount(), $player);
				$ev->call();
			}
		}

		return $array;
	}

	public function sellChest(Player $player, Chest $chest, bool $literally = true) : array{
		/** @var SkyBlockPlayer $player */
		$isession = $player->getGameSession()->getIslands();
		$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();
		$array = [
			"count" => 0,
			"price" => 0,
		];

		if(is_null($island)) return $array;

		$level = $island->getSizeLevel();

		foreach($chest->getInventory()->getContents() as $slot => $item){
			$price = $this->getValue($item, $level);

			if($price > -1){
				$array["price"] += $price;
				$array["count"] += $item->getCount();

				if($literally){
					if(is_null(($shopitem = $this->getShopItem($item)))){
						$player->sendMessage(TextFormat::RED . $item->getName());
						continue;
					}

					$ev = new ShopSellEvent($shopitem, $item->getCount(), $player);
					$ev->call();

					$player->addTechits($price);
					$chest->getInventory()->clear($slot);
					
				}
			}
		}
		return $array;
	}


	public function chestMode(Player $player) : bool{
		if(isset($this->chestmode[$player->getName()])){
			unset($this->chestmode[$player->getName()]);
			return false;
		}
		return $this->chestmode[$player->getName()] = true;
	}

	public function inChestMode(Player $player) : bool{
		return $this->chestmode[$player->getName()] ?? false;
	}

}
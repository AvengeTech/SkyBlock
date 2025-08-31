<?php

namespace skyblock\shop\data;

use skyblock\shop\ShopPrices;

use core\ui\elements\simpleForm\Button;
use core\utils\BlockRegistry;
use pocketmine\item\ItemBlock;
use pocketmine\item\StringToItemParser;
use skyblock\generators\block\AutoMiner;
use skyblock\generators\block\DimensionalBlock;
use skyblock\generators\block\OreGenerator;
use skyblock\spawners\block\MobSpawner;

class ShopCategory {

	/** @var ShopItem[] $items */
	private array $items = [];
	private Button $button;

	public function __construct(
		private int $level
	){
		foreach(ShopPrices::SHOP_ITEMS[$level] as $key => $data){
			$item = StringToItemParser::getInstance()->parse($key);

			if (is_null($item)){
				echo "Null at $level item $key.\n";
				continue;
			}

			$bp = $data[0] ?? -1;
			$sp = $data[1] ?? -1;
			$le = $data[2] ?? "";
			$customName = $data[3] ?? "";
			$extra = $data[4] ?? [];

			if($item instanceof ItemBlock){
				if($item->getBlock() instanceof OreGenerator){
					BlockRegistry::ORE_GENERATOR()->addData(
						$item, 
						$extra[0], 
						$extra[1], 
						0
					);
				}elseif($item->getBlock() instanceof DimensionalBlock) {
					BlockRegistry::DIMENSIONAL_BLOCK()->addData(
						$extra[0], 
						0, 
						$item
					);
				}elseif($item->getBlock() instanceof AutoMiner) {
					$b = BlockRegistry::AUTOMINER();
					$b->addData($item);
				}elseif($item->getBlock() instanceof MobSpawner){
					$b = BlockRegistry::MOB_SPAWNER();
					$b->addData($item, 1);
				}
			}

			$si = new ShopItem($level, $item, $bp, $sp, $le, $customName, $extra);
			
			$this->items[$key] = $si;
		}
		$this->button = new Button("Level " . $this->getLevel() . " Shop");
	}

	public function getLevel() : int{ return $this->level; }

	/** @return ShopItem[] */
	public function getItems() : array{ return $this->items; }

	public function getButton() : Button{ return $this->button; }
}

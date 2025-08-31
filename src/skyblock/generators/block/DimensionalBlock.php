<?php namespace skyblock\generators\block;

use pocketmine\block\Element;
use pocketmine\item\Item;

use core\utils\TextFormat;
use skyblock\generators\tile\DimensionalTile;

class DimensionalBlock extends Element{

	public function getPickedItem(bool $addUserData = false) : Item{
		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof DimensionalTile){
			return $this->addData($tile->getLevel(), $tile->getBoost(), $this->asItem());
		}

		return $this->asItem();
	}

	public function getDrops(Item $item) : array{
		$drop = $this->asItem();
		/** @var DimensionalTile $tile */
		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof DimensionalTile){
			$this->addData($tile->getLevel(), $tile->getBoost(), $drop);
		}
		return [$drop];
	}

	public function addData(int $level, int $boost, Item $item) : Item{
		$item->getNamedTag()
		->setInt(DimensionalTile::TAG_LEVEL, $level)
		->setInt(DimensionalTile::TAG_BOOST, $boost);

		$item->setCustomName(TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Dimensional Block");

		$lores = [];
		$lores[] = "Placing this will cause items";
		$lores[] = "from different dimensions to";
		$lores[] = "warp to your island! Make sure";
		$lores[] = "you place a container above it!";
		$lores[] = " ";
		$lores[] = "Level: " . TextFormat::YELLOW . $level;
		$lores[] = "Boost: " . TextFormat::AQUA . number_format($boost) . " blocks";

		foreach($lores as $key => $lore){
			$lores[$key] = TextFormat::RESET . TextFormat::GRAY . $lore;
		}
		$item->setLore($lores);

		return $item;
	}

	public function onScheduledUpdate() : void{
		$tile = $this->position->getWorld()->getTile($this->position);

		if($tile instanceof DimensionalTile && $tile->onUpdate()){
			$rate = $tile->getRate(true) * 20;
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, $tile->hasBoost() ? $rate / 2 : $rate);
		}
	}
}
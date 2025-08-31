<?php

namespace skyblock\generators\block;

use pocketmine\block\Element;
use pocketmine\item\Item;

use core\utils\TextFormat;

use skyblock\generators\tile\AutoMiner as TileAutoMiner;
use skyblock\generators\Structure;

class AutoMiner extends Element{

	public function getPickedItem(bool $addUserData = false) : Item{
		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof TileAutoMiner){
			return $this->addData(
				$this->asItem(), 
				[TileAutoMiner::DATA_HORIZONTAL => $tile->getHorizontalExtender(), TileAutoMiner::DATA_VERTICAL => $tile->getVerticalExtender()]
			);
		}

		return $this->asItem();
	}

	public function getDrops(Item $item) : array{
		$drop = $this->asItem();

		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof TileAutoMiner){
			$this->addData(
				$drop, 
				[TileAutoMiner::DATA_HORIZONTAL => $tile->getHorizontalExtender(), TileAutoMiner::DATA_VERTICAL => $tile->getVerticalExtender()]
			);
		}

		return [$drop];
	}

	public function addData(Item $item, array $extender = []) : Item{
		if(empty($extender)) $extender = [TileAutoMiner::DATA_HORIZONTAL => 0, TileAutoMiner::DATA_VERTICAL => 0];

		$item->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "AutoMiner");
		$item->getNamedTag()->setIntArray(TileAutoMiner::TAG_EXTENDER, $extender);

		$lores = [];
		$lores[] = "Place this above the block";
		$lores[] = "you would like to be automatically";
		$lores[] = "mined! Place a container above it to";
		$lores[] = "automatically store items!";
		$lores[] = " ";
		$lores[] = "Extensions:";
		$lores[] = " - Horizontal: " . TextFormat::DARK_GREEN . Structure::EXTENDER[$extender[TileAutoMiner::DATA_HORIZONTAL]] . " blocks";
		$lores[] = " - Vertical: " . TextFormat::GREEN . Structure::EXTENDER[$extender[TileAutoMiner::DATA_VERTICAL]] . " blocks";
		foreach($lores as $key => $lore){
			$lores[$key] = TextFormat::RESET . TextFormat::GRAY . $lore;
		}
		$item->setLore($lores);

		return $item;
	}

	public function onScheduledUpdate() : void{
		$tile = $this->getPosition()->getWorld()->getTile($this->getPosition());

		if ($tile instanceof TileAutoMiner) {
			if ($tile->canUpdate()) {
				if ($tile->onUpdate()) $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 200);
			} else $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 200);
		}
	}

}
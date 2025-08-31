<?php namespace skyblock\generators\block;

use pocketmine\block\Element;
use pocketmine\item\Item;

use skyblock\generators\tile\OreGenerator as TileOreGenerator;

use core\utils\{
	TextFormat
};
use skyblock\generators\Structure;

class OreGenerator extends Element{

	public function getPickedItem(bool $addUserData = false) : Item{
		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof TileOreGenerator){
			return $this->addData(
				$this->asItem(), 
				$tile->getType(), 
				$tile->getLevel(), 
				$tile->getBoost(),
				[TileOreGenerator::DATA_HORIZONTAL => $tile->getHorizontalExtender(), TileOreGenerator::DATA_VERTICAL => $tile->getVerticalExtender()],
				[TileOreGenerator::DATA_LEVEL => $tile->getSolidifierLevel(), TileOreGenerator::DATA_RUNS => $tile->getSolidifierRuns()]
			);
		}

		return $this->asItem();
	}

	public function getDrops(Item $item) : array{
		$drop = $this->asItem();
		/** @var TileOreGenerator $tile */
		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof TileOreGenerator){
			$this->addData(
				$drop, 
				$tile->getType(), 
				$tile->getLevel(), 
				$tile->getBoost(),
				[TileOreGenerator::DATA_HORIZONTAL => $tile->getHorizontalExtender(), TileOreGenerator::DATA_VERTICAL => $tile->getVerticalExtender()],
				[TileOreGenerator::DATA_LEVEL => $tile->getSolidifierLevel(), TileOreGenerator::DATA_RUNS => $tile->getSolidifierRuns()]
			);
		}
		return [$drop];
	}

	public function addData(Item $item, int $type, int $level = 1, int $boost = 0, array $extender = [], array $solidifier = []) : Item{
		if(empty($extender)) $extender = [TileOreGenerator::DATA_HORIZONTAL => 0, TileOreGenerator::DATA_VERTICAL => 0];
		if(empty($solidifier)) $solidifier = [TileOreGenerator::DATA_LEVEL => 0, TileOreGenerator::DATA_RUNS => 0];

		if($solidifier[TileOreGenerator::DATA_LEVEL] == 0) $solidifier[TileOreGenerator::DATA_RUNS] = 0;

		$typeName = ucfirst(str_replace(['_ore', '_'], ['', ' '], TileOreGenerator::ORES[$type]));

		$item->setCustomName(TextFormat::RESET . TextFormat::AQUA . $typeName . " Generator");
		$item->getNamedTag()
		->setInt(TileOreGenerator::TAG_TYPE, $type)
		->setInt(TileOreGenerator::TAG_LEVEL, $level)
		->setInt(TileOreGenerator::TAG_BOOST, $boost)
		->setIntArray(TileOreGenerator::TAG_EXTENDER, $extender)
		->setIntArray(TileOreGenerator::TAG_SOLIDIFIER, $solidifier);

		$lores = [];
		$lores[] = "Place this to make ores";
		$lores[] = "generate above it!";
		$lores[] = " ";
		$lores[] = "Level: " . TextFormat::YELLOW . $level;
		$lores[] = "Boost: " . TextFormat::AQUA . number_format($boost) . " blocks";
		$lores[] = " ";
		$lores[] = "Extensions:";
		$lores[] = " - Horizontal: " . TextFormat::DARK_GREEN . Structure::EXTENDER[$extender[TileOreGenerator::DATA_HORIZONTAL]] . " blocks";
		$lores[] = " - Vertical: " . TextFormat::GREEN . Structure::EXTENDER[$extender[TileOreGenerator::DATA_VERTICAL]] . " blocks";
		$lores[] = " ";
		$lores[] = "Solidifier:";
		$lores[] = " - Level: " . TextFormat::DARK_PURPLE . $solidifier[TileOreGenerator::DATA_LEVEL];
		$lores[] = " - Runs: " . TextFormat::LIGHT_PURPLE . $solidifier[TileOreGenerator::DATA_RUNS];

		foreach($lores as $key => $lore){
			$lores[$key] = TextFormat::RESET . TextFormat::GRAY . $lore;
		}
		$item->setLore($lores);

		return $item;
	}

	public function onScheduledUpdate() : void{
		$pos = $this->getPosition();
		$tile = $pos->getWorld()->getTile($pos);
		if($tile instanceof TileOreGenerator && $tile->onUpdate()){
			$rate = $tile->getRate(true) * 20;
			$pos->getWorld()->scheduleDelayedBlockUpdate($pos, $tile->hasBoost() ? $rate / 2 : $rate);
		}
	}
}
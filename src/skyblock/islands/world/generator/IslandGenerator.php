<?php namespace skyblock\islands\world\generator;

use pocketmine\math\Vector3;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

abstract class IslandGenerator extends Generator {

	abstract public function getName(): string;

	public function getSettings() : array{
		return [];
	}

	public function getType() : string{
		return explode("_", $this->getName())[1];
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		return;
	}

	abstract public static function getWorldSpawn() : Vector3;

	abstract public static function getChestPosition() : Vector3;

	abstract public static function getIslandEntityPosition(): Vector3;

}
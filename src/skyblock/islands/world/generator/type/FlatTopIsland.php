<?php namespace skyblock\islands\world\generator\type;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\object\OakTree;
use pocketmine\math\Vector3;

use skyblock\islands\world\generator\IslandGenerator;

class FlatTopIsland extends IslandGenerator{

	public function getName() : string{
		return "island_flat_top";
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if($chunkX == 0 && $chunkZ == 0){
			for($x = 0; $x <= 15; $x++){
				for($z = 0; $z <= 15; $z++){
					$chunk->setBlockStateId($x, 61, $z, VanillaBlocks::GRASS()->getStateId());
					$chunk->setBlockStateId($x, 60, $z, VanillaBlocks::STONE()->getStateId());
				}
			}
			$chunk->setBlockStateId(8, 59, 8, VanillaBlocks::BEDROCK()->getStateId());

			$tree = new OakTree();
			$transaction = $tree->getBlockTransaction($world, 3, 62, 3, $this->random);
			$transaction->apply();

			$chunk->setBlockStateId(12, 62, 12, VanillaBlocks::CHEST()->getStateId());
			$world->setChunk($chunkX, $chunkZ, $chunk);
		}
	}

	public static function getWorldSpawn() : Vector3{
		return new Vector3(8, 62, 8);
	}

	public static function getChestPosition() : Vector3{
		return new Vector3(12, 62, 12);
	}

	public static function getIslandEntityPosition() : Vector3{
		return new Vector3(3.5, 62, 12.5);
	}

}
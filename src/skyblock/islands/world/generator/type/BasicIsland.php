<?php namespace skyblock\islands\world\generator\type;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\object\OakTree;
use pocketmine\math\Vector3;

use skyblock\islands\world\generator\IslandGenerator;

class BasicIsland extends IslandGenerator{

	public function getName() : string{
		return "island_basic";
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if($chunkX == 0 && $chunkZ == 0){
			for($x = 6; $x < 12; $x++){
				for($z = 6; $z < 12; $z++){
					$chunk->setBlockStateId($x, 60, $z, VanillaBlocks::STONE()->getStateId());
					$chunk->setBlockStateId($x, 61, $z, VanillaBlocks::STONE()->getStateId());
					$chunk->setBlockStateId($x, 62, $z, VanillaBlocks::DIRT()->getStateId());
					$chunk->setBlockStateId($x, 63, $z, VanillaBlocks::GRASS()->getStateId());
				}
			}
			for($airX = 9; $airX < 12; $airX++){
				for($airZ = 9; $airZ < 12; $airZ++){
					$chunk->setBlockStateId($airX, 60, $airZ, VanillaBlocks::AIR()->getStateId());
					$chunk->setBlockStateId($airX, 61, $airZ, VanillaBlocks::AIR()->getStateId());
					$chunk->setBlockStateId($airX, 62, $airZ, VanillaBlocks::AIR()->getStateId());
					$chunk->setBlockStateId($airX, 63, $airZ, VanillaBlocks::AIR()->getStateId());
				}
			}
			$chunk->setBlockStateId(6, 60, 6, VanillaBlocks::BEDROCK()->getStateId());

			$tree = new OakTree();
			$transaction = $tree->getBlockTransaction($world, 11, 64, 6, $this->random);
			$transaction->apply();

			$chunk->setBlockStateId(7, 64, 11, VanillaBlocks::CHEST()->getStateId());
			$world->setChunk($chunkX, $chunkZ, $chunk);
		}
	}

	public static function getWorldSpawn() : Vector3{
		return new Vector3(8, 66, 8);
	}

	public static function getChestPosition() : Vector3{
		return new Vector3(7, 64, 11);
	}

	public static function getIslandEntityPosition() : Vector3{
		return new Vector3(6.5, 64, 6.5);
	}

}
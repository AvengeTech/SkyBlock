<?php namespace skyblock\islands\world\provider;

use pocketmine\math\Vector3;
use pocketmine\world\format\io\WorldData;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;

class IslandWorldData implements WorldData{

	public function save() : void{
	}

	public function getName() : string{
		return "Unknown";
	}

	public function setName(string $value): void {
	}

	public function getGenerator() : string{
		return "void";
	}

	public function getGeneratorOptions() : string{
		return "";
	}

	public function getSeed() : int{
		return 0;
	}

	public function getTime() : int{
		return World::TIME_DAY;
	}

	public function setTime(int $value) : void{
	}

	public function getSpawn() : Vector3{
		return new Vector3(0, 0, 0);
	}

	public function setSpawn(Vector3 $pos) : void{
	}

	public function getDifficulty() : int{
		return World::DIFFICULTY_NORMAL;
	}

	public function setDifficulty(int $difficulty) : void{
	}

	public function getRainTime() : int{
		return 0;
	}

	public function setRainTime(int $ticks) : void{
	}

	public function getRainLevel() : float{
		return 0;
	}

	public function setRainLevel(float $level) : void{
	}

	public function getLightningTime() : int{
		return 0;
	}

	public function setLightningTime(int $ticks) : void{
	}

	public function getLightningLevel() : float{
		return 0;
	}

	public function setLightningLevel(float $level) : void{
	}
}
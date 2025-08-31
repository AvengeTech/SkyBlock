<?php namespace skyblock\combat\arenas;

use pocketmine\math\Vector3;
use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\combat\Combat;
use skyblock\combat\arenas\commands\{
	ArenaCommand,
	SpawnTest
};

class Arenas{

	public Arena $arena;

	public function __construct(
		public SkyBlock $plugin,
		public Combat $combat
	){
		$plugin->getServer()->getCommandMap()->register("arena", new ArenaCommand($plugin, "arena", "Teleport to the Warzone"));
		$plugin->getServer()->getCommandMap()->register("arena", new SpawnTest($plugin, "st", "Spawn test for sd/mb"));
		$this->setupArena();
	}

	public function getCombat() : Combat{
		return $this->combat;
	}

	public function setupArena() : void{
		$arenas = [
			ArenaData::WARZONE_PVPMINE,
		];
		$data = $arenas[mt_rand(0, count($arenas) - 1)];
		
		$this->arena = new Arena("arena", ($data["locked"] ?? false), $data["name"], $data["world"], $this->setupPositions($data["spawnpoints"]), $data["corners"], new Vector3(...$data["center"]), $this->setupPositions($data["supply_drops"]), $this->setupPositions($data["money_bags"]));
	}

	public function setupPositions(array $positions) : array{
		foreach($positions as $key => $array){
			$positions[$key] = new Vector3(...$array);
		}
		return $positions;
	}

	public function getTotalPlayers() : int{
		return count($this->getArena()->getPlayers()); //can prob remove
	}

	public function getArena() : Arena{
		return $this->arena;
	}

	public function doArenaTick() : void{
		$this->getArena()->tick();
	}

	public function inArena(Player $player) : bool{
		return $this->getArena()->inArena($player);
	}

}
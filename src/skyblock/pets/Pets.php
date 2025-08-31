<?php

namespace skyblock\pets;

use skyblock\pets\command\MyPets;
use skyblock\pets\command\PetGuide;
use skyblock\pets\command\SpawnPet;
use skyblock\SkyBlock;

class Pets{
	
	public function __construct(
		private SkyBlock $plugin
	){
		$plugin->getServer()->getCommandMap()->registerAll("Pets", [
			new SpawnPet($plugin, "spawnpet", "Test command to spawn pets"),
			new MyPets($plugin, "mypets", "mypets selection"),
			new PetGuide($plugin, "petguide", "Guide for Pets")
		]);
	}

	/** @return int[] */
	public function getPetsByRarity(int $rarity) : array{
		$pets = [];

		foreach(Structure::PETS as $id => $data){
			if($data[Structure::DATA_RARITY] === $rarity) $pets[] = $id;
		}

		return $pets;
	}
}
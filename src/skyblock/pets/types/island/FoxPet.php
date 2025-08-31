<?php

namespace skyblock\pets\types\island;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\pets\types\IslandPet;

class FoxPet extends IslandPet{

	public function __construct(
		Location $location,
		?CompoundTag $nbt = null
	){
		parent::__construct($location, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.7, 0.6); }
	
	public static function getNetworkTypeId() : string{ return EntityIds::FOX; }

	public function getName() : string{ return "Fox"; }
}
<?php

namespace skyblock\pets\types\island;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\pets\types\IslandPet;

class AxolotlPet extends IslandPet{

	public function __construct(
		Location $location,
		?CompoundTag $nbt = null
	){
		parent::__construct($location, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.42, 0.75); }
	
	public static function getNetworkTypeId() : string{ return EntityIds::AXOLOTL; }

	public function getName() : string{ return "Axolotl"; }

}
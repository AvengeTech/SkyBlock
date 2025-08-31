<?php

namespace skyblock\pets\types\island;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use skyblock\pets\types\FlyingPet;
use skyblock\pets\types\IslandPet;

class BeePet extends IslandPet implements FlyingPet{

	public function __construct(
		Location $location,
		?CompoundTag $nbt = null
	){
		parent::__construct($location, $nbt);
	}

	protected function getInitialDragMultiplier() : float{ return 0.07; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.5, 0.55); }
	
	public static function getNetworkTypeId() : string{ return EntityIds::BEE; }

	public function getName() : string{ return "Bee"; }
}
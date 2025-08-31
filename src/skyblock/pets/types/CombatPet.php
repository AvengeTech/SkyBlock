<?php

namespace skyblock\pets\types;

use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

/**
 * MAYBE
 * FOR
 * A
 * FUTURE
 * UPDATE
 */
class CombatPet extends EntityPet{

	const MODE_ATTACK = 4;
	const MODE_STUNNED = 5;

	public function __construct(
		private PetData $petData,
		Location $location,
		?CompoundTag $nbt = null
	){
		parent::__construct($petData, $location, $nbt);
	}
}
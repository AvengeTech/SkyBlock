<?php

namespace skyblock\entity;

use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class NoGravityItemEntity extends ItemEntity {



	public function __construct(Location $location, Item $item, protected ?CompoundTag $nbt = null) {
		if ($this->hasOwner()) $this->setOwner($nbt->getString('owner'));
		parent::__construct($location, $item, $nbt);
	}

	public function getGravity(): float {
		return 0;
	}

	public function saveNBT(): CompoundTag {
		$nbt = parent::saveNBT();

		if (!is_null($this->nbt->getString('owner', null))) $nbt->setString('owner', $this->nbt->getString('owner'));
		return $nbt;
	}

	public function spawnTo(Player $player): void {
		if (!$this->hasOwner() || $this->getOwner() == $player->getXuid()) parent::spawnTo($player);
	}

	public function hasOwner(): bool {
		return $this->nbt->getString('owner', false) !== false;
	}
}

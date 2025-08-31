<?php namespace skyblock\islands\text\entity;

use pocketmine\entity\{
	Entity,
	EntitySizeInfo,
	Location
};
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;

use skyblock\islands\text\Text;

class TextEntity extends Entity{

	protected function getInitialDragMultiplier(): float
	{
		return 0;
	}

	protected function getInitialGravity(): float
	{
		return 0;
	}

	public function __construct(Location $loc, ?CompoundTag $nbt, public Text $text){
		parent::__construct($loc, $nbt);

		$this->setNametag($text->getFormattedText());
		$this->setNametagAlwaysVisible(true);
		$this->setScale(0.0000001);
	}

	public static function getNetworkTypeId() : string{
		return "minecraft:wolf"; //awooooo
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$changedProperties = $this->getDirtyNetworkData();
		if(count($changedProperties) > 0){
			$this->sendData(null, $changedProperties);
			$this->getNetworkProperties()->clearDirtyProperties();
		}
		return $this->isAlive();
	}

	public function onUpdate(int $currentTick) : bool{
		return $this->entityBaseTick(1);
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0, 0, 0);
	}

}

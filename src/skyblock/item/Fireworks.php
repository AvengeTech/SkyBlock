<?php

namespace skyblock\item;

use pocketmine\block\Block;
use pocketmine\block\utils\DyeColor;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\utils\Binary;
use skyblock\entity\FireworksEntity;

class Fireworks extends Item{

	protected const TAG_FIREWORK_DATA = "Fireworks"; //TAG_Compound
	protected const TAG_FLIGH_DURATION = "Flight"; //TAG_Byte
	protected const TAG_EXPLOSIONS = "Explosions"; //TAG_List
	
	public const EXPLOSION_TYPE = "FireworkType"; //TAG_Byte
	public const EXPLOSION_COLOR = "FireworkColor"; //TAG_ByteArray
	public const EXPLOSION_FADE = "FireworkFade"; //TAG_ByteArray
	public const EXPLOSION_TWINKLE = "FireworkFlicker"; //TAG_Byte
	public const EXPLOSION_TRAIL = "FireworkTrail"; //TAG_Byte

	const TYPE_SMALL_SPHERE = 0;
	const TYPE_HUGE_SPHERE = 1;
	const TYPE_STAR = 2;
	const TYPE_CREEPER_HEAD = 3;
	const TYPE_BURST = 4;

	const TYPES = [
		"small_sphere" => self::TYPE_SMALL_SPHERE,
		"huge_sphere" => self::TYPE_HUGE_SPHERE,
		"star" => self::TYPE_STAR,
		"creeper_head" => self::TYPE_CREEPER_HEAD,
		"burst" => self::TYPE_BURST
	];

	public function getFlightDuration() : int{
		return $this->getExplosionsTag()->getByte(self::TAG_FLIGH_DURATION, 1);
	}

	public function getRandomizedFlightDuration() : int{
		return ($this->getFlightDuration() + 1) * 10 + mt_rand(0, 5) + mt_rand(0, 6);
	}

	public function setFlightDuration(int $duration) : self{
		$this->getExplosionsTag()->setByte(self::TAG_FLIGH_DURATION, $duration);

		return $this;
	}

	public function setExplosion(int $type, DyeColor $color, string $fade = "", bool $flicker = false, bool $trail = false) : self{
		$tag = $this->getExplosionsTag();
		$explosions = $tag->getListTag(self::TAG_EXPLOSIONS);

		if($explosions === null){
			$tag->setTag(self::TAG_EXPLOSIONS, $explosions = new ListTag());
		}

		$explosions->push(CompoundTag::create()
			->setByte(self::EXPLOSION_TYPE, $type)
			->setByteArray(self::EXPLOSION_COLOR, Binary::writeByte(DyeColorIdMap::getInstance()->toInvertedId($color)))
			->setByteArray(self::EXPLOSION_FADE, $fade)
			->setByte(self::EXPLOSION_TWINKLE, $flicker ? 1 : 0)
			->setByte(self::EXPLOSION_TRAIL, $trail ? 1 : 0)
		);

		return $this;
	}

	protected function getExplosionsTag() : CompoundTag{
		$tag = $this->getNamedTag()->getCompoundTag(self::TAG_FIREWORK_DATA);

		if(is_null($tag)){
			$this->getNamedTag()->setTag(self::TAG_FIREWORK_DATA, $tag = CompoundTag::create());
		}
		return $tag;
	}



	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult{
		$entity = new FireworksEntity(Location::fromObject($blockReplace->getPosition()->add(0.5, 0, 0.5), $player->getWorld(), lcg_value() * 360, 90), $this);

		$this->pop();
		$entity->spawnToAll();
		//TODO: what if the entity was marked for deletion?
		return ItemUseResult::SUCCESS();
	}
}
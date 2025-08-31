<?php

namespace skyblock\pets\item;

use core\utils\TextFormat as TF;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\pets\Structure;
use skyblock\pets\types\EntityPet;
use skyblock\pets\types\PetData;
use skyblock\SkyBlockPlayer;

class PetEgg extends Item{

	public const TAG_INIT = "init";
	public const TAG_IDENTIFIER = "identifier";
	public const TAG_PET_DATA = "pet_data";

	public const TAG_PET_NAME = "pet_name";
	public const TAG_PET_LEVEL = "pet_level";
	public const TAG_PET_XP = "pet_xp";
	public const TAG_PET_ENERGY = "pet_energy";

	private int $identifier = -1;
	private ?PetData $petData = null;

	public function setup(int $identifier, ?PetData $data = null) : self{
		$this->identifier = $identifier;
		$this->petData = $data;

		return $this;
	}

	public function getIdentifier() : int{ return $this->identifier; }

	public function setPetData(?PetData $data) : self{
		$this->petData = $data;
		return $this;
	}

	public function hasPetData() : bool{ return !is_null($this->petData); }

	public function init() : self{
		$this->getNamedTag()->setByte(self::TAG_INIT, 1);

		$name = Structure::PETS[$this->identifier][Structure::DATA_NAME];
		$rarity = Structure::PETS[$this->identifier][Structure::DATA_RARITY];

		$this->setCustomName(TF::RESET . ED::rarityColor($rarity) . ED::rarityName($rarity) . " Pet Egg");
		$lores = [];
		$lores[] = TF::GRAY . "Use this item to add the pet";
		$lores[] = TF::GRAY . "to your pets list.";
		$lores[] = TF::GRAY . " ";
		$lores[] = TF::YELLOW . "Type: " . ED::rarityColor($rarity) . ucfirst($name);
		$lores[] = TF::GRAY . " ";

		if($this->hasPetData()){
			$lores[] = TF::GRAY . "Name: " . TF::GOLD . $this->petData->getName();
			$lores[] = TF::GRAY . "Level: " . TF::AQUA . $this->petData->getLevel();
			$lores[] = TF::GRAY . "Xp: " . TF::BLUE . $this->petData->getXp();
			$lores[] = TF::GRAY . "Energy: " . TF::YELLOW . $this->petData->getEnergy();
			$lores[] = TF::GRAY . " ";
		}

		$lores[] = TF::GRAY . "Tap/Right-Click to use this item.";
		foreach($lores as $key => $lore) $lores[$key] = TF::RESET . $lore;

		$this->setLore($lores);
		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([]));

		return $this;
	}

	/** @param SkyBlockPlayer $player */
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
		if(!isset(Structure::PETS[$this->identifier])) return ItemUseResult::FAIL();

		$session = $player->getGameSession()->getPets();

		if($session->hasPet($this->identifier)){
			$player->sendMessage(TF::RI . "You already own this pet.");

			return ItemUseResult::FAIL();
		}

		$this->pop();

		if($this->hasPetData()){
			$petData = $this->petData;
		}else{
			$petData = new PetData(
				$this->identifier,
				Structure::PETS[$this->identifier][Structure::DATA_NAME],
				1,
				0
			);
			$petData->setEnergy($petData->getMaxEnergy());
		}

		if(is_null($session->getActivePet())){
			/** @var EntityPet $class */
			$class = Structure::PETS[$this->identifier][Structure::DATA_CLASS];

			$pet = new $class(
				$player->getLocation()
			);
			$pet->setPetData($petData);
			$pet->setOwner($player);
			$pet->updateNameTag();
			$pet->spawnToAll();
	
			$session->addPet($petData);
			$session->setActivePet($pet);

			$player->sendMessage(TF::GI . "This pet has been set as your current active pet.");
		}else{
			$session->addPet($petData);
			$player->sendMessage(TF::GI . "Since you already have an active pet. The " . ED::rarityColor($petData->getRarity()) . $petData->getName() . TF::GRAY . " was added to your \"My Pets\" list.");
		}

		return ItemUseResult::SUCCESS();
	}

	protected function deserializeCompoundTag(CompoundTag $tag): void{
		parent::deserializeCompoundTag($tag);

		$this->identifier = $tag->getInt(self::TAG_IDENTIFIER, -1);

		/** @var CompoundTag $petData */
		$petData = $tag->getTag(self::TAG_PET_DATA);

		if(!is_null($petData)){
			$name = $petData->getString(self::TAG_PET_NAME, Structure::PETS[$this->identifier][Structure::DATA_NAME]);
			$level = $petData->getInt(self::TAG_PET_LEVEL, 1);
			$xp = $petData->getInt(self::TAG_PET_XP, 0);
			$energy = $petData->getFloat(self::TAG_PET_ENERGY, 0);

			$this->petData = new PetData(
				$this->identifier,
				$name,
				$level,
				$xp,
				$energy
			);
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		if($tag->getByte(self::TAG_INIT, 0) === 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));

		$tag->setInt(self::TAG_IDENTIFIER, $this->identifier);

		if(!is_null($this->petData)){
			$tag->setTag(
				self::TAG_PET_DATA, 
				CompoundTag::create()
				->setString(self::TAG_PET_NAME, $this->petData->getName())
				->setInt(self::TAG_PET_LEVEL, $this->petData->getLevel())
				->setInt(self::TAG_PET_XP, $this->petData->getXp())
				->setFloat(self::TAG_PET_ENERGY, $this->petData->getEnergy())
			);
		}
	}
	
}
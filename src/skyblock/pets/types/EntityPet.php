<?php

namespace skyblock\pets\types;

use core\utils\TextFormat as TF;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\pets\event\PetExhaustEvent;
use skyblock\pets\event\PetFeedEvent;
use skyblock\pets\event\PetModeSwitchEvent;
use skyblock\pets\item\EnergyBooster;
use skyblock\pets\item\GummyOrb;
use skyblock\pets\item\PetFeed;
use skyblock\pets\Structure;
use skyblock\pets\uis\PetInfoUI;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

abstract class EntityPet extends Living{

	const MODE_NONE = 0;
	const MODE_IDLE = 1;
	const MODE_ROAMING = 2; // future
	const MODE_FOLLOWING = 3;

	const BLOCKS_TILL_TELEPORT = 15;

	protected ?SkyBlockPlayer $owner = null;
	protected int $mode = self::MODE_FOLLOWING;

	protected float $jumpVelocity = 0.5;

	private ?PetData $petData = null;

	public function __construct(
		Location $location,
		?CompoundTag $nbt = null
	){
		parent::__construct($location, $nbt);

		$this->setNameTagVisible(true);
		$this->setMovementSpeed(($this instanceof IslandPet ? 0.60 : 0.70));
	}

	public function getUpdatedNameTag() : string{
		$owner = $this->owner;
		$name = "";

		if(!is_null($owner)){
			if(!is_null($owner->getGameSession()?->getIslands()->getIslandAt()) && !is_null($this->getPetData())){
				$name .= TF::BOLD . TF::YELLOW . $this->getPetData()->getName();
			}else{
				$name .= TF::BOLD . TF::YELLOW . $this->getOwner()->getName() . "'s";
			}
		}else{
			$name .= TF::BOLD . TF::YELLOW . "Unknown's";
		}

		return TF::BOLD . TF::YELLOW . $name . "\n" . ($this->getPetData()?->getDefaultName() . " " ?? "") . "Pet" . TF::AQUA . " [" . TF::WHITE . $this->petData->getLevel() . TF::AQUA . "]" . ((($this->petData->getEnergy() / $this->getPetData()->getMaxEnergy()) * 100) > 2 ? "" : "\n" . TF::RED . "!LOW ENERGY!");
	}

	public function updateNameTag() : void{
		$this->setNametag($this->getUpdatedNameTag());
	}

	public function getName() : string{ return "Pet"; }

	public function getMaxHealth() : int{ return 10; }

	public function canSaveWithChunk() : bool{ return false; }

	public static function getNetworkTypeId() : string{ return EntityIds::PLAYER; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.5, 0.5); }

	public function getOwner() : ?SkyBlockPlayer{ return $this->owner; }

	public function setOwner(?SkyBlockPlayer $owner) : self{
		$this->owner = $owner;

		// Something in the pet data is probably gonna get changed if the owner is being set.
		// so just put this here.
		if(!is_null($owner)){
			$session = $owner->getGameSession()->getPets();
			$session->setChanged();
		}

		return $this;
	}

	public function getPetData() : ?PetData{ return $this->petData; }

	public function setPetData(?PetData $data) : self{
		$this->petData = $data;
		return $this;
	}

	public function getMode() : int{ return $this->mode; }

	public function setMode(int $mode) : self{
		$this->mode = $mode;
		return $this;
	}

	public function feed(Player $player, PetFeed $food) : bool{
		$ev = new PetFeedEvent($player, $this->petData, $food);

		if(!$food->isInitiated()) return false;

		if(
			$food instanceof EnergyBooster && 
			!$this->petData->atMaxEnergy()
		){
			$this->petData->addEnergy($food->getEnergy());

			$player->sendMessage(TF::GI . "You restored " . TF::YELLOW . $food->getEnergy() . TF::EMOJI_LIGHTNING . TF::GRAY . " of your pets energy!");
		}elseif(
			$food instanceof GummyOrb &&
			!$this->petData->isMaxLevel()
		){
			$this->petData->addXp($food->getXP(), $player);

			$player->sendMessage(TF::GI . "You added " . TF::BLUE . $food->getXP() . TF::GRAY . " to your pets xp!");
		}

		$ev->call();
		$food->pop();
		$player->getInventory()->setItemInHand($food);

		return true;
	}

	protected function entityBaseTick(int $tickDiff = 1): bool{
		parent::entityBaseTick($tickDiff);

		if(is_null($this->owner)) return false;

		if($this->getNameTag() !== $this->getUpdatedNameTag()) $this->updateNameTag();

		if(
			SkyBlock::getInstance()->getCombat()->getArenas()->inArena($this->owner) || 
			SkyBlock::getInstance()->getKoth()->inGame($this->owner) ||
			SkyBlock::getInstance()->getLms()->inGame($this->owner)
		){
			$this->petData->rest();
			$this->owner->getGameSession()?->getPets()->setActivePet(null);
			$this->owner->sendMessage(TF::RI . "Your pet has been set to rest, pets can not enter a combat zone.");

			if(!($this->isClosed() || $this->isFlaggedForDespawn())) $this->flagForDespawn();
			return false;

			return false;
		}

		if($this->petData->getEnergy() < 1){
			$ev = new PetExhaustEvent($this->owner, $this->petData);
			$ev->call();

			$this->petData->rest();
			$this->getOwner()?->sendToastNotification(
				ED::rarityColor($this->petData->getRarity()) . $this->petData->getDefaultName() . " Pet",
				TF::RED . "Your pet has run out of energy!"
			);
			$this->getOwner()?->getGameSession()?->getPets()->setActivePet(null);

			if(!($this->isClosed() || $this->isFlaggedForDespawn())) $this->flagForDespawn();
			return false;
		}

		if(!$this->justCreated && $this->ticksLived % (20 * (60 * 5)) === 0){
			$this->petData->subEnergy(Structure::ENERGY_DEPLETION[$this->petData?->getRarity()]);
			return true;
		}

		return true;
	}

	/** @param SkyBlockPlayer $player */
	public function onInteract(Player $player, Vector3 $clickPos) : bool{
		if($this->getOwner()?->getXuid() !== $player->getXuid() || is_null($this->getOwner())) return false;

		if(
			!$player->getGameSession()->getIslands()->atValidIsland() && 
			!($player->isTier3() || $player->isSn3ak())
		){
			$player->sendMessage(TF::RI . "You can only interact with your pet at your island.");
			return false;
		}

		if(($item = $player->getInventory()->getItemInHand()) instanceof PetFeed){
			$this->feed($player, $item);
			return true;
		}

		$this->mode = ($this->mode === self::MODE_FOLLOWING ? self::MODE_IDLE : self::MODE_FOLLOWING);

		$ev = new PetModeSwitchEvent($player, $this->petData);
		$ev->call();

		if($this->mode === self::MODE_FOLLOWING){
			$player->sendMessage(TF::GI . "Your pet is now following you.");
		}else{
			$player->sendMessage(TF::RI . "Your pet is no longer following you.");
		}

		return true;
	}

	public function attack(EntityDamageEvent $source) : void{
		if(!$source instanceof EntityDamageByEntityEvent) return;

		$player = $source->getDamager();

		if(!$player instanceof SkyBlockPlayer) return;

		$player->showModal(new PetInfoUI($player, $this));
	}
}
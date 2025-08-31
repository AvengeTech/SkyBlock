<?php namespace skyblock\fishing\entity;

use core\AtPlayer;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use core\utils\TextFormat;
use pocketmine\math\RayTraceResult;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\block\{
	Lava,
	Water,
};

use skyblock\{
	SkyBlock, 
	SkyBlockPlayer
};
use skyblock\crates\Crates;
use skyblock\entity\ArmorStand;
use skyblock\fishing\item\FishingRod;
use skyblock\fishing\event\FishingCatchEvent;
use skyblock\enchantments\ItemData;

use core\techie\TechieBot;
use core\vote\entity\VoteBox;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\Enchantments;
use skyblock\fishing\event\FishingPreTugEvent;
use skyblock\fishing\event\FishingReelEvent;
use skyblock\fishing\event\FishingTugEvent;
use skyblock\fishing\Structure as FishingStructure;
use skyblock\pets\Structure;
use skyblock\settings\SkyBlockSettings;

class Hook extends Projectile{

	const LIQUID_NONE = 0;
	const LIQUID_WATER = 1;
	const LIQUID_LAVA = 2;

	private float $width = 0.25;
	private float $height = 0.25;

	protected bool $touchedLiquid = false;
	protected int $liquidType = self::LIQUID_NONE;

	private int $fakeHookBobbing = 50;

	private bool $tugging = false;
	private int $tugTime = -1;
	private int $nextTug = -1;

	private ?Vector3 $liquidFace = null;

	public function __construct(
		Location $loc, 
		?Entity $shootingEntity = null, 
		private ?FishingRod $fishingRod = null
	){
		parent::__construct($loc, $shootingEntity);

		if(!$shootingEntity instanceof Player){
			$this->flagForDespawn();
			return;
		}
		$this->fishingRod = $fishingRod;

		$this->setNextTug();

		$this->networkPropertiesDirty = true;
	}

	public function isTouchingLiquid() : bool{ return $this->touchedLiquid; }

	public function getLiquidType() : int{ return $this->liquidType; }

	public static function getNetworkTypeId() : string{ return EntityIds::FISHING_HOOK; }

	protected function getInitialDragMultiplier(): float { return 0.01; }

	protected function getInitialGravity() : float{ return 0.0225; }

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo($this->height, $this->width);
	}

	public function isFireProof() : bool{
		return true;
		$item = $this->fishingRod;

		if(is_null($item)) return false;

		return $item->hasEnchantment(EnchantmentRegistry::THERMAL_HOOK()->getEnchantment());
	}

	public function canSaveWithChunk() : bool{ return false; }

	public function getName() : string{ return "Hook"; }

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed) return false;

		if(!($player = $this->getOwningEntity()) instanceof Player){
			$this->flagForDespawn();
			return false;
		}
		if($player->getPosition()->distance($this->getPosition()) > 30){
			$this->flagForDespawn();
			return false;
		}

		if(!$this->touchedLiquid && $this->liquidType == self::LIQUID_NONE){
			switch($this->isCollided){
				case true:
					foreach($this->getBlocksAroundWithEntityInsideActions() as $block){
						if($block instanceof Water){
							$this->touchedLiquid = true;
							$this->liquidType = self::LIQUID_WATER;
							break;
						}
					}
					break;

				case false:
					$face = $this->getPosition()->getSide(Facing::DOWN);
					if(
						$this->getWorld()->getBlock($face) instanceof Lava &&
						$this->getPosition()->distance($face->add(0, 1, 0)) <= 1 &&
						$this->fishingRod->hasEnchantment(EnchantmentRegistry::THERMAL_HOOK()->getEnchantment())
					){
						$this->touchedLiquid = true;
						$this->liquidType = self::LIQUID_LAVA;
						$this->liquidFace = $face->add(0, 0.625, 0);

						$this->setMotion(Vector3::zero());
						$this->setPosition($this->liquidFace);
						break;
					}
					break;
			}

			if($this->touchedLiquid){
				$pk = new ActorEventPacket();
				$pk->actorRuntimeId = $this->getId();
				$pk->eventId = ActorEvent::FISH_HOOK_POSITION;
				NetworkBroadcastUtils::broadcastPackets($this->getViewers(), [$pk]);
			}
		}

		if(!$this->touchedLiquid) return true;

		if($this->liquidType === self::LIQUID_LAVA){
			$this->setMotion(Vector3::zero());

			$this->noClientPredictions = true;
			$this->gravityEnabled = false;

			if($this->fakeHookBobbing >= 20){
				$this->setPosition($this->getPosition()->add(0, 0.0035, 0));
			}elseif($this->fakeHookBobbing >= 0){
				$this->setPosition($this->getPosition()->add(0, -0.0035, 0));
			}

			if($this->fakeHookBobbing <= 0){
				$this->fakeHookBobbing = 40;
			}else{
				$this->fakeHookBobbing--;
			}
		}

		($this->isTugging() ? $this->tickTug() : $this->tickNext());

		return $this->isAlive();
	}

	protected function move(float $dx, float $dy, float $dz): void{
		if(
			$this->touchedLiquid && 
			$this->liquidType === self::LIQUID_LAVA && 
			!is_null($this->liquidFace) && 
			$this->getPosition()->getY() <= $this->liquidFace->getY()
		) return;

		parent::move($dx, $dy, $dz);
	}

	public function tickTug() : bool{
		$this->tugTime--;

		$player = $this->getOwningEntity();

		if(!$player instanceof Player) return false;

		$player->sendTip(TextFormat::GREEN . "Fish is tugging! " . TextFormat::AQUA . $this->tugTime);
		if($this->tugTime < 0){
			$player->sendTip(TextFormat::RED . "Missed the catch!");
			$this->setTugging(false);
			$this->setNextTug();
			return false;
		}
		return true;
	}

	public function tickNext() : bool{
		$this->nextTug--;
		if($this->nextTug <= 0){
			$this->setTugging();
			$this->nextTug = -1;
			return false;
		}
		return true;
	}

	public function getTugTime() : int{ return $this->tugTime; }

	public function isTugging() : bool{ return $this->tugging; }

	public function setTugging(bool $bool = true) : void{
		if($bool){
			/** @var AtPlayer */
			$player = $this->getOwningEntity();
			$player->getAntiCheatSession()->reeltime = microtime(true);

			$pk = new ActorEventPacket();
			$pk->actorRuntimeId = $this->getId();
			$pk->eventId = ActorEvent::FISH_HOOK_HOOK;
			NetworkBroadcastUtils::broadcastPackets($this->getViewers(), [$pk]);

			$ev = new FishingTugEvent($player, $this->fishingRod, 20);
			$ev->call();

			$this->tugTime = $ev->getTugTime();
		}else{
			$this->tugTime = -1;
		}
		$this->tugging = $bool;
	}

	public function setNextTug() : void{
		$tug = mt_rand(100, 400);
		if(!is_null($this->fishingRod)){
			$ev = new FishingPreTugEvent($this->getOwningEntity(), $this->fishingRod, $tug);
			$ev->call();

			$tug = $ev->getNextTug();

			// PET BUFF
			$owner = $this->getOwningEntity();

			if($owner instanceof SkyBlockPlayer){
				$pSession = $owner->getGameSession()->getPets();
				$pet = $pSession->getActivePet();

				if(!is_null($pet)){
					$petData = $pet->getPetData();
					$buffData = array_values($petData->getBuffData());

					if($petData->getIdentifier() === Structure::CAT){
						$increaseChance = $buffData[0];

						if(round(lcg_value() * 100, 2) <= $increaseChance){
							$tug -= mt_rand(250, 350);
						}
					}
				}
			}
		}
		$this->nextTug = max(10, $tug);
	}

	public function reel(?FishingRod $rod = null) : bool{
		$rod = $rod ?? $this->fishingRod;

		if($this->closed) return false;

		$player = $this->getOwningEntity();
		if(!$player instanceof SkyBlockPlayer){
			$this->flagForDespawn();
			return false;
		}

		$event = new FishingReelEvent($player, $rod);
		$event->call();

		// Could as an enchantment, currently disabled
		// if (!$this->touchedLiquid && $rod !== null && $rod->hasEnchantment(EnchantmentRegistry::FLING()->getEnchantment()) && $player->isSneaking() && $player->isStaff()) {
		// 	$rod->drag($this, $player, $rod->getEnchantment(EnchantmentRegistry::FLING()->getEnchantment())->getLevel() * 0.8);
		// 	$this->flagForDespawn();
		// 	return false;
		// }

		if($event->isCancelled()){
			$this->flagForDespawn();
			return false;
		}

		$gs = $player->getGameSession();
		$gs->getFishing()->addCatch();

		$as = $player->getAntiCheatSession();

		if($this->isTugging()){
			$as->reeledRod();
			if(!is_null($rod)){
				$catgories = $event->getExtraData()["categories"] ?? [];
				$multi = $event->getExtraData()["multi"] ?? 1;

				$find = SkyBlock::getInstance()->getFishing()->getRandomFind(($this->liquidType === self::LIQUID_LAVA), $catgories, $multi, $gs->getFishing());
				$ev = new FishingCatchEvent($player, $rod, $find, $this->liquidType);
				$ev->call();

				$data = new ItemData($rod);
				$chance = ($tl = $data->getTreeLevel(ItemData::SKILL_LOOT)) * 5;

				if(mt_rand(1, 100) <= $chance){
					$find->give($player, false);
					if($tl > 3) $find->give($player, false);
				}

				// PET BUFF
				$owner = $this->getOwningEntity();

				if($owner instanceof SkyBlockPlayer){
					$pSession = $owner->getGameSession()->getPets();
					$pet = $pSession->getActivePet();

					if(!is_null($pet)){
						$petData = $pet->getPetData();
						$buffData = array_values($petData->getBuffData());
						
						if(!$petData->isMaxLevel() && round(lcg_value() * 100, 2) <= 25.07) $petData->addXp(mt_rand(1, 10), $owner);
	
						if($petData->getIdentifier() === Structure::AXOLOTL){
							$doubleChance = $buffData[0];

							if(round(lcg_value() * 100, 2) <= $doubleChance){
								$find->give($player, false);
							}

							if(count($buffData) > 1){
								$tripleChance = $buffData[1];

								if(round(lcg_value() * 100, 2) <= $tripleChance){
									$find->give($player, false);
								}
							}
						}
					}
				}

				$find->give($player, true, $rod !== null ? ItemData::SKILL_TREES[ItemData::SKILL_EXP][$data->getTreeLevel(ItemData::SKILL_EXP)] ?? 1 : 1);

				$essence = 0;

				if(round(lcg_value() * 100, 2) <= 63.25){
					$essence = (round(lcg_value() * 100, 2) <= 65 ? mt_rand(1, 5) : mt_rand(5, 10));

					if($essence > 0){
						if ($this->fishingRod->hasEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())) {
							$essence += $this->fishingRod->getEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())->getLevel() + 1;
						}

						if(($essenceLvl = $data->getTreeLevel(ItemData::SKILL_ESSENCE)) > 0){
							$essence *= ItemData::SKILL_TREES[ItemData::SKILL_ESSENCE][$essenceLvl];
						}

						$essence = (int) $essence;
						
						$gs->getEssence()->addEssence($essence);
					}
				}
				
				if(
					(($rod->getMaxDurability() - $rod->getDamage()) / $rod->getMaxDurability()) * 100 <= 45 &&
					$player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::TOOL_BREAK_ALERT)
				){
					$player->sendTip(TextFormat::EMOJI_CAUTION . TextFormat::YELLOW . " Tool has " . TextFormat::RED . ($rod->getMaxDurability() - $rod->getDamage()) . TextFormat::YELLOW . " durability left!");
				}else{
					$player->sendTip(TextFormat::GREEN . "Nice catch!" . ($essence < 1 ? "" : TextFormat::AQUA . " (You found {$essence} Essence)"));
				}
			}
			
			$this->flagForDespawn();
			return true;
		}

		$this->flagForDespawn();
		return false;
	}

	public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$player = $this->getOwningEntity();
		if(!$player instanceof SkyBlockPlayer) return;
		if(
			$entityHit instanceof TechieBot ||
			$entityHit instanceof ArmorStand ||
			$entityHit instanceof VoteBox
		) return;

		parent::onHitEntity($entityHit, $hitResult);
		$session = $player->getGameSession()->getFishing();
		$session->setHooked($entityHit);
	}
}

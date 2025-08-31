<?php namespace skyblock\spawners\entity;

use core\AtPlayer;
use pocketmine\block\{
	Block,
	BlockTypeIds,
    Flowable
};
use pocketmine\player\Player;
use pocketmine\entity\{
	Entity,
	Living,
	Location
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\item\Sword;
use pocketmine\math\{
	Vector3
};
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\utils\TextFormat as TF;

use core\utils\Utils;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\Enchantments;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\enchantments\ItemData;
use skyblock\islands\permission\Permissions;
use skyblock\pets\Structure;
use skyblock\pets\types\EntityPet;
use skyblock\settings\SkyBlockSettings;
use skyblock\spawners\entity\hostile\CaveSpider;
use skyblock\spawners\entity\hostile\Husk;
use skyblock\spawners\entity\hostile\Spider;
use skyblock\spawners\entity\hostile\WitherSkeleton;
use skyblock\spawners\entity\passive\Pig;
use skyblock\spawners\event\SpawnerKillEvent;

abstract class Mob extends Living{

	/** SPAWNER */
	public const STACK_DISTANCE = 5;
	public const SELF_DESTRUCT = 1200;

	/** HOSTILE MOVEMENT */
	public const FIND_DISTANCE = 15;
	public const LOSE_DISTANCE = 25;

	protected int $stackValue = 1;
	protected int $lifeTimer = self::SELF_DESTRUCT;

	protected int $findNewTargetTicks = 0;

	protected ?Vector3 $randomPosition = null;
	protected $findNewPositionTicks = 200;

	protected float $jumpVelocity = 0.5;

	protected int $attackWait = 20;
	
	protected int $attackDamage = 4;
	protected float $speed = 0.35;

	/** Passive */
	protected int $walkTicks = -1;
	protected int $idleTicks = 0;
	protected int $panicTicks = 0;

	/** Knockback */
	protected int $knockbackTicks = 0;

	private ?Item $itemInHand = null;

	public function __construct(
		Location $location,
		?CompoundTag $nbt = null,
		protected bool $movement = false, 
		protected bool $stacks = true
	){
		parent::__construct($location, $nbt);
		$this->setNameTagVisible(true);
		$this->updateNameTag();
		$this->setMovementSpeed(($this instanceof Passive ? 0.60 : 0.70));
	}

	public function updateNameTag() : void{
		$this->setNametag(($this instanceof Passive ? TF::AQUA : TF::RED) . TF::BOLD . $this->getName() . TF::YELLOW . " (" . $this->getStackValue() . ")" . TF::RESET . TF::GREEN . " " . $this->getHealth() . "/" . $this->getMaxHealth());
	}

	public function getItemInHand() : ?Item{
		return $this->itemInHand;
	}

	public function setItemInHand(Item $itemInHand) : void{
		$this->itemInHand = $itemInHand;

		foreach($this->getViewers() as $viewer){
			$viewer->getNetworkSession()->sendDataPacket(MobEquipmentPacket::create(
				$this->getId(), 
				ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getItemInHand() ?? VanillaItems::AIR())), 
				0, 
				0, 
				ContainerIds::INVENTORY
			));
		}
	}

	protected function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);

		if(is_null($this->getItemInHand())) return;

		$player->getNetworkSession()->sendDataPacket(MobEquipmentPacket::create(
			$this->getId(), 
			ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getItemInHand() ?? VanillaItems::AIR())), 
			0, 
			0, 
			ContainerIds::INVENTORY
		));
	}

	public function getDrops() : array{
		$drops = $this->getArmorInventory()->getContents();

		if(!is_null($this->itemInHand) && !($this->itemInHand->isNull())){
			$drops[] = $this->itemInHand;
		}

		return $drops;
	}

	public function canSaveWithChunk() : bool{ return false; }

	public function canStack() : bool{ return $this->stacks; }

	public function getStackValue() : int{ return $this->stackValue; }

	public function addStackValue(int $value = 1) : void{
		$this->setStackValue($this->getStackValue() + $value);
	}

	public function subStackValue(int $value = 1) : void{
		$this->setStackValue($this->getStackValue() - $value);
	}

	public function setStackValue(int $value) : void{
		$this->stackValue = $value;
		$this->updateNameTag();
	}

	public function kill() : void{
		if($this->getStackValue() == 1){
			parent::kill();
		}else{
			$this->doDeath();
			$this->subStackValue();
			$this->setHealth($this->getMaxHealth());
		}

		$cause = $this->getLastDamageCause();

		if($cause instanceof EntityDamageByEntityEvent){
			$player = $cause->getDamager();
			if ($player instanceof SkyBlockPlayer && !$player->isVanished()) {
				$ev = new SpawnerKillEvent($this, $player);
				$ev->call();

				if(!$player->getGameSession()->getIslands()->atIsland()){
					$player->getGameSession()->getCombat()->addMob();
				}
			}
		}
	}

	public function doDeath() : void{
		$this->playSound("death");
		
		$cause = $this->getLastDamageCause();

		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();

			if (!($damager instanceof SkyBlockPlayer) || $damager->isVanished()) return;

			$xp = $this->getXpDropAmount();
			$drops = $this->getDrops();
			$item = $damager->getInventory()->getItemInHand();

			if($item instanceof Sword){
				$data = new ItemData($item);

				if(($xpLvl = $data->getTreeLevel(ItemData::SKILL_EXP)) > 0){
					$xp *= ItemData::SKILL_TREES[ItemData::SKILL_EXP][$xpLvl];
				}

				$chance = ($tl = $data->getTreeLevel(ItemData::SKILL_LOOT)) * 5;

				if(mt_rand(1, 100) <= $chance){
					foreach($drops as $drop){
						$drop->setCount($drop->getCount() * ($tl > 3 ? 3 : 2));
					}
				}

				if(round(lcg_value() * 100, 2) <= 7.5){
					$essence = (round(lcg_value() * 100, 2) <= 85 ? mt_rand(1, 5) : mt_rand(5, 10));

					if($item->hasEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())){
						$essence += $item->getEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())->getLevel() + 1;
					}
					
					$session = $damager->getGameSession()->getEssence();
					$session->addEssence((int) $essence);
					$damager->sendTip(TF::AQUA . "You found {$essence} Essence");
				}

				// PET BUFF
				$pSession = $damager->getGameSession()->getPets();
				$pet = $pSession->getActivePet();

				if(!is_null($pet)){
					$petData = $pet->getPetData();
					$buffData = array_values($petData->getBuffData());

					if(!$petData->isMaxLevel() && round(lcg_value() * 100, 2) <= 4.55) $petData->addXp(mt_rand(1, 5), $damager);

					if($petData->getIdentifier() === Structure::ALLAY){

						$doubleChance = $buffData[0];

						if(round(lcg_value() * 100 , 2) < $doubleChance){
							foreach($drops as $drop){
								$drop->setCount($drop->getCount() * 2);
							}
						}elseif(count($buffData) > 1){ // made elseif just in case they both activate at the same time.
							if(round(lcg_value() * 100 , 2) < $doubleChance){
								foreach($drops as $drop){
									$drop->setCount($drop->getCount() * 3);
								}
							}
						}
					}
				}
			}

			$session = $damager->getGameSession()->getSettings();

			if($session->getSetting(SkyBlockSettings::AUTO_INV)){
				if(count(($left = $damager->getInventory()->addItem(...$drops))) > 0){
					$damager->sendTip(TF::RED . "Your inventory is full!");
				}

				foreach($left as $item){
					$this->getWorld()->dropItem($this->getPosition(), $item);
				}
			}else{
				foreach($drops as $item){
					$this->getWorld()->dropItem($this->getPosition(), $item);
				}
			}
			
			if($session->getSetting(SkyBlockSettings::AUTO_XP)){
				$damager->getXpManager()->addXp(floor($xp));
			}else{
				Utils::dropTempExperience($this->getWorld(), $this->getPosition(), floor($xp));
			}
		}else{
			foreach($this->getDrops() as $item){
				$this->getWorld()->dropItem($this->getPosition(), $item);
			}
			$nearest = $this->getNearestPlayer(6);
			if($nearest instanceof Player){
				Utils::dropTempExperience($this->getWorld(), $this->getPosition(), $this->getXpDropAmount());
			}
		}
	}

	public function onDeathUpdate(int $tickDiff) : bool{
		if($this->deadTicks < $this->maxDeadTicks){
			$this->deadTicks += $tickDiff;

			if($this->deadTicks >= $this->maxDeadTicks){
				$this->endDeathAnimation();

				$cause = $this->getLastDamageCause();

				if($cause instanceof EntityDamageByEntityEvent) {
					$damager = $cause->getDamager();

					if ($damager instanceof SkyBlockPlayer && !$damager->isVanished()) {
						$session = $damager->getGameSession()->getSettings();

						if($session->getSetting(SkyBlockSettings::AUTO_XP)){
							$damager->getXpManager()->addXp($this->getXpDropAmount());
						}else{
							Utils::dropTempExperience($this->getWorld(), $this->getPosition(), $this->getXpDropAmount());
						}
					}
				}else{
					$nearest = $this->getNearestPlayer(6);
					if($nearest instanceof Player){
						Utils::dropTempExperience($this->getWorld(), $this->getPosition(), $this->getXpDropAmount());
					}
				}
			}
		}
		return $this->deadTicks >= $this->maxDeadTicks;
	}

	public function getNearestEntity(float $maxDistance) : ?Mob{
		$pos = $this->getPosition();

		$currentTargetDistSq = $maxDistance ** 2;
		$currentTarget = null;

		foreach($pos->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy($maxDistance, $maxDistance, $maxDistance), $this) as $entity){
			if($entity::getNetworkTypeId() !== $this::getNetworkTypeId() || $entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity->isAlive()){
				continue;
			}
			$distSq = $entity->getPosition()->distanceSquared($pos);
			if($distSq < $currentTargetDistSq){
				$currentTargetDistSq = $distSq;
				$currentTarget = $entity;
			}
		}
		return $currentTarget;
	}

	public function getNearestPlayer(float $maxDistance) : ?Player{
		return $this->getWorld()->getNearestEntity($this->getPosition(), $maxDistance, Player::class);
	}

	public function attack(EntityDamageEvent $source) : void{
		$this->knockbackTicks = mt_rand(4, 6);

		if($source instanceof EntityDamageByEntityEvent && !$source->isCancelled()){
			$player = $source->getDamager();

			if ($player instanceof SkyBlockPlayer && !$player->isVanished()) {
				if(!$this->hasMovement()){
					$isession = $player->getGameSession()->getIslands();

					if($isession->atIsland()){
						$island = $isession->getIslandAt();
						$perm = $island->getPermissions()->getPermissionsBy($player) ?? $island->getPermissions()->getDefaultVisitorPermissions();

						if(!$perm->getPermission(Permissions::KILL_SPAWNER_MOBS)){
							$source->cancel();
						}
					}
				}else{
					if(!$this instanceof Passive && (mt_rand(0, 2) === 1 && $this->getTargetEntity() !== $player)){
						$this->setTargetEntity($player);
					}
				}

				if(!$source->isCancelled()){
					if(mt_rand(1, 3) === 1){
						$item = $player->getInventory()->getItemInHand();

						if($item instanceof Sword){
							$data = new ItemData($item);
							$leveledUp = $data->addXp(mt_rand(1, 3));
							$data->getItem()->setLore($data->calculateLores());
							$data->send($player);
							if($leveledUp){
								$data->sendLevelUpTitle($player);
							}
						}
					}
				}
			}

			if($this instanceof Passive){
				$this->panicTicks = mt_rand(60, 120);
				$this->idleTicks = -1;
				$this->walkTicks = $this->panicTicks + mt_rand(0, 40);
				$this->generateRandomPosition();
			}

			$this->lifeTimer = self::SELF_DESTRUCT;
		}

		parent::attack($source);

		$this->playSound(($this instanceof Pig ? 'say' : 'hurt') . mt_rand(1, 2));
		$this->updateNameTag();
	}

	public function hasMovement() : bool{ return $this->movement; }

	public function setMovement(bool $movement = true) : void{ $this->movement = $movement; }

	public function getSoundPrefix() : string{
		return strtolower($this->getName());
	}

	public function playSound(string $type) : void{
		$pk = new PlaySoundPacket();
		$pk->soundName = "mob" . $this->getSoundPrefix() . $type;
		$pk->x = (int) $this->location->getX();
		$pk->y = (int) $this->location->getY();
		$pk->z = (int) $this->location->getZ();
		$pk->volume = 50;
		$pk->pitch = 1;
		foreach($this->getViewers() as $player){
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		parent::entityBaseTick($tickDiff);

		if($this->hasMovement()){
			if($this instanceof Hostile){
				if(!is_null($this->getTargetEntity())){
					$this->attackTarget();
				}

				if($this->findNewTargetTicks > 0){
					$this->findNewTargetTicks--;
				}
				if(!!is_null($this->getTargetEntity()) && $this->findNewTargetTicks === 0){
					$this->findNewTarget();
				}

				$this->walk();
			}else{ //Friendly
				$this->walk();
			}
		}

		if($this->canStack() && $this->ticksLived % (SkyBlock::isLaggy() ? 160 : 80) == 0) {
			$nearest = $this->getNearestEntity(self::STACK_DISTANCE);
			if($nearest !== null){
				$stack = $nearest->getStackValue();
				if($stack <= $this->getStackValue() && !$this->isFlaggedForDespawn()){
					$nearest->flagForDespawn();
					$this->addStackValue($stack);
				}
			}
		}

		$this->lifeTimer--;
		if($this->lifeTimer <= 0 && !$this->isFlaggedForDespawn() && !$this->isClosed()){
			$this->flagForDespawn();
		}

		return $this->isAlive();
	}
	
	/** Movement sht */
	
	
	/** HOSTILE */
	public function attackTarget() : bool{
		$target = $this->getTargetEntity();

		if(is_null($target)) return false;
		if(!$target instanceof Living) return false;

		if ($target instanceof AtPlayer) {
			if(
				$target->isVanished() ||
				$target->getPosition()->distance($this->getPosition()) > self::FIND_DISTANCE
			){
				$this->setTargetEntity(null);
				$this->findNewTarget();
				return false;
			}
		}

		$pos = $this->getPosition();

		if($pos->distance($target->getPosition()) <= $this->getScale() + 0.3 && $this->attackWait <= 0){
			//todo: calculate damage multiplied from held item
			if(!is_null($this->itemInHand) && !$this->itemInHand->isNull() && $this->itemInHand instanceof Durable){
				$this->itemInHand->applyDamage(1);
			}

			$event = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->itemInHand?->getAttackPoints() ?? $this->getBaseAttackDamage());
			if($target->getHealth() - $event->getFinalDamage() <= 0){
				$event->cancel();

				if($target instanceof SkyBlockPlayer) $target->getGameSession()->getCombat()->ded($this);

				$this->setTargetEntity(null);
				$this->findNewTarget();
			}

			$target->attack($event);

			if($this instanceof WitherSkeleton){
				$target->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * mt_rand(5, 10), mt_rand(0, 2)));
			}elseif($this instanceof Husk){
				$target->getEffects()->add(new EffectInstance(VanillaEffects::HUNGER(), 20 * mt_rand(5, 10), mt_rand(0, 2)));
			}elseif($this instanceof CaveSpider){
				$target->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 20 * mt_rand(5, 10), mt_rand(0, 2)));
			}
			
			$this->broadcastAnimation(new ArmSwingAnimation($this));
			$this->attackWait = 20;
		}

		$this->attackWait--;
		return $this->isAlive();
	}

	//Targetting//
	public function findNewTarget() : void{
		$distance = self::FIND_DISTANCE;
		$target = null;
		foreach($this->getPosition()->getWorld()->getPlayers() as $player){
			/** @var AtPlayer $player */
			if (($dis = $player->getPosition()->distance($this->getPosition())) <= $distance && !$player->isVanished()) {
				$distance = $dis;
				$target = $player;
			}
		}
		$this->findNewTargetTicks = 60;
		$this->setTargetEntity($target);
	}

	public function atRandomPosition() : bool{
		return $this->getRandomPosition() === null || $this->getPosition()->distance($this->getRandomPosition()) <= 2;
	}

	public function getRandomPosition() : ?Vector3{
		return $this->randomPosition;
	}

	public function generateRandomPosition() : void{
		$pos = $this->getPosition();
		$x = mt_rand(-10, 10) + $pos->x;
		$z = mt_rand(-10, 10) + $pos->z;

		// set a real y coordinate ...
		$y = $this->findTargetFloor($x, $z);
		$this->randomPosition = new Vector3($x, $y, $z);
	}

	public function findTargetFloor(float $x, float $z) : float{
		$pos = $this->getPosition();

		if($this->getWorld()->getBlock(new Vector3($x, $pos->y, $z))->getTypeId() === BlockTypeIds::AIR){
			return $pos->y;
		}
		for($yScan = 1; $yScan < 3; $yScan++){
			if($this->getWorld()->getBlock(new Vector3($x, $pos->y + $yScan, $z))->getTypeId() === BlockTypeIds::AIR){
				return $pos->y + $yScan;
			}
		}
		for($yScan = -1; $yScan > -3; $yScan--){
			if($this->getWorld()->getBlock(new Vector3($x, $pos->y + $yScan, $z))->getTypeId() === BlockTypeIds::AIR){
				return $pos->y + $yScan;
			}
		}
		return $pos->y;
	}

	public function getSpeed() : float{
		$speed = ($this->isUnderwater() ? $this->getMovementSpeed() / 2 : $this->getMovementSpeed());
		return $this->panicTicks > 0 ? $speed * 2 : $speed;
	}

	public function getBaseAttackDamage() : int{
		return $this->attackDamage;
	}

	public function getFrontBlock(float $y = 0) : Block{
		$frontBlock = $this->getPosition()->getWorld()->getBlock($this->getPosition())->getSide($this->getHorizontalFacing());
		
		return $this->getPosition()->getWorld()->getBlock($frontBlock->getPosition()->add(0, $y, 0));
	}

	public function shouldJump() : bool{
		return $this->isCollidedHorizontally && (
			$this->getFrontBlock(1)->getTypeId() === BlockTypeIds::AIR &&
			$this->getFrontBlock(2)->getTypeId() === BlockTypeIds::AIR &&
			$this->getPosition()->getWorld()->getBlock($this->getPosition()->add(0, 2, 0))->getTypeId() === BlockTypeIds::AIR
		) || !($this->getFrontBlock() instanceof Flowable);

		// ($this->getFrontBlock()->getTypeId() != BlockTypeIds::AIR || $this->getFrontBlock(-1) instanceof Stair) ||
		// ($this->getWorld()->getBlock($this->getPosition()->asVector3()->add(0,-0,5)) instanceof Slab &&
		// 	(!$this->getFrontBlock(-0.5) instanceof Slab && $this->getFrontBlock(-0.5)->getTypeId() != BlockTypeIds::AIR)) &&
		// $this->getFrontBlock(1)->getTypeId() === BlockTypeIds::AIR &&
		// $this->getFrontBlock(2)->getTypeId() === BlockTypeIds::AIR &&
		// !$this->getFrontBlock() instanceof Flowable &&
		// $this->jumpTicks == 0;
	}

	public function walk() : void{
		if($this->knockbackTicks > 0){
			$this->knockbackTicks--;
			return;
		}

		if($this->findNewPositionTicks > 0) $this->findNewPositionTicks--;

		$pos = $this->getPosition();

		if(is_null($this->getTargetEntity())){
			if($this->atRandomPosition() || $this->findNewPositionTicks === 0){
				$this->generateRandomPosition();
				$this->findNewPositionTicks = 200;
				return;
			}

			$position = $this->getRandomPosition();
		}else{
			$position = $this->getTargetEntity()->getPosition();

			$this->lookAt($position);
		}

		$x = $position->x - $pos->getX();
		$y = $position->y - $pos->getY();
		$z = $position->z - $pos->getZ();

		if($this->shouldJump()){
			$this->jump();
			$this->checkObstruction($x, $y, $z);
		}

		if($this->isCollidedHorizontally && $this instanceof Spider){
			$this->motion->y = 0.35;
		}

		if($x * $x + $z * $z < $this->getScale()){
			$this->motion->x = 0;
			$this->motion->z = 0;
		}else{
			$this->motion->x = $this->getMovementSpeed() * 0.35 * ($x / (abs($x) + abs($z)));
			$this->motion->z = $this->getMovementSpeed() * 0.35 * ($z / (abs($x) + abs($z)));
		}

		$this->setRotation(rad2deg(atan2(-$x, $z)), 0);
	}
}
<?php

namespace skyblock\enchantments;

use core\AtPlayer;
use core\Core;
use core\settings\GlobalSettings;
use pocketmine\block\{
    Bamboo,
    BlockTypeIds,
    Cactus,
    Carrot,
    ChorusPlant,
    Crops,
    Dirt,
    Farmland,
    Grass,
    Melon,
    MelonStem,
    NetherWartPlant,
    Opaque,
    Pumpkin,
    PumpkinStem,
    Stem,
    Sugarcane,
    VanillaBlocks,
    Wheat,
    Wood
};
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\player\Player;
use pocketmine\item\{
	Armor,
	Durable,
    Item,
    ItemBlock,
    ItemTypeIds,
	VanillaItems
};
use pocketmine\entity\effect\{
	EffectInstance,
	VanillaEffects
};
use pocketmine\entity\Living;

use pocketmine\event\entity\{
	EntityDamageByEntityEvent
};
use pocketmine\event\block\{
	BlockBreakEvent
};
use pocketmine\network\mcpe\protocol\{
	AddActorPacket,
	types\LevelSoundEvent,
	LevelEventPacket,
	LevelSoundEventPacket,
	PlaySoundPacket,
	RemoveActorPacket,
	types\LevelEvent
};

use skyblock\SkyBlock;
use skyblock\combat\arenas\entity\{
	MoneyBag,
	SupplyDrop
};
use skyblock\settings\SkyBlockSettings;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\spawners\entity\Mob;

use core\utils\{
    BlockRegistry,
    PlaySound,
	GenericSound,
    ItemRegistry,
    TextFormat
};
use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\EnchantmentTableParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\particle\PortalParticle;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\particle\SplashParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\BlockBreakSound;
use pocketmine\world\sound\LaunchSound;
use skyblock\block\RedMushroomBlock;
use skyblock\crates\Crates;
use skyblock\crates\event\KeyFindEvent;
use skyblock\enchantments\utils\EnchantmentUtils;
use skyblock\pets\block\PetBox;
use skyblock\SkyBlockPlayer;
use skyblock\techits\item\TechitNote;

class EnchantmentCalls {

	const MODIFIER_KABOOM = 50;
	const MODIFIER_SPITE = 51;
	const MODIFIER_LIFESTEAL = 51;
	const MODIFIER_ZEUS = 52;
	const MODIFIER_COMBO = 53;
	const MODIFIER_DODGE = 55;

	const MODIFIER_DAMAGE_CORRECTION = 54;

	public array $event = [];

	public array $equip = [];
	public array $unequip = [];

	public array $task = [];

	public function __construct() {
		$this->event = [
			//Pickaxe
			ED::FEED => function (BlockBreakEvent $e, int $level) {
				$block = $e->getBlock();
				$player = $e->getPlayer();

				if (mt_rand(1, 5) == 1 && $block instanceof Opaque) {
					if ($player instanceof AtPlayer && $player->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $player->getWorld()->addSound($player->getPosition(), new GenericSound($player->getPosition(), LevelSoundEvent::BURP));

					$player->getHungerManager()->setFood(min($player->getHungerManager()->getFood() + 2, 20));
					$player->getHungerManager()->setSaturation(min($player->getHungerManager()->getFood() + 2, 20));
				}
			},
			ED::FEATHER_WEIGHT => function (BlockBreakEvent $e, int $level) {
				if (mt_rand(1, 15 - (min(3, $level) * 3)) == $level) {
					$e->getPlayer()->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), ($level == 4 ? 5 : $level) * 20 * 3, min(2, $level - 1)));
				}
			},
			ED::KEY_MAGNET => function (BlockBreakEvent $e, int $level) {
				for (
					$i = 1;
					$i <= $level;
					$i++
				) {
					SkyBlock::getInstance()->getCrates()->excavate($e->getPlayer(), $e->getBlock());
				}
			},
			ED::AUTOSMELT => function (BlockBreakEvent $e, int $level) {
				$convert = [
					ItemTypeIds::RAW_IRON => VanillaItems::IRON_INGOT(),
					ItemTypeIds::RAW_GOLD => VanillaItems::GOLD_INGOT(),
					ItemTypeIds::RAW_COPPER => VanillaItems::COPPER_INGOT()
				];
				$drops = $e->getDrops();
				foreach ($drops as $key => $drop) {
					if (isset($convert[$drop->getTypeId()])) {
						$drops[$key] = $convert[$drop->getTypeId()]->setCount($drop->getCount());
					}
				}
				$e->setDrops($drops);
			},
			ED::SIFT => function (BlockBreakEvent $e, int $level) {
				$multipliers = [
					ItemTypeIds::COAL,
					ItemTypeIds::IRON_INGOT,
					ItemTypeIds::IRON_NUGGET,
					ItemTypeIds::GOLD_INGOT,
					ItemTypeIds::GOLD_NUGGET,
					-BlockTypeIds::IRON_ORE,
					-BlockTypeIds::GOLD_ORE,
					ItemTypeIds::NETHER_QUARTZ,
					ItemTypeIds::REDSTONE_DUST,
					ItemTypeIds::DIAMOND,
					ItemTypeIds::EMERALD
				];
				$drops = $e->getDrops();
				foreach ($drops as $key => $drop) {
					if (in_array($drop->getTypeId(), $multipliers)) {
						if (mt_rand(1, (6 - $level)) === 1) {
							$drops[$key] = $drop->setCount($drop->getCount() * mt_rand(2, $level + 2));
						}
					}
				}
				$e->setDrops($drops);
			},
			ED::MAGNIFY => function (BlockBreakEvent $e, int $level) {
				$item = $e->getItem();
				$block = $e->getBlock();

				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 10) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 8) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 5) == 1;
						break;
					case 4:
						$chance = mt_rand(1, 3) == 1;
						break;
				}

				if ($chance) {
					if (($xp = $e->getXpDropAmount()) > 0) {
						($player = $e->getPlayer())->getXpManager()->addXp($xp * ($level + 1));
						$player->getWorld()->broadcastPacketToViewers($player->getPosition(), LevelEventPacket::create(LevelEvent::SOUND_ORB, 0, $player->getPosition()));
					}
				}
			},
			ED::TRANSFUSION => function (BlockBreakEvent $e, int $level) {
				$player = $e->getPlayer();
				$block = $e->getBlock();
				$lvl = $block->getPosition()->getWorld();

				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 6;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 12;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 15;
						break;
					case 4:
						$chance = mt_rand(1, 100) <= 18;
						break;
					case 5:
						$chance = mt_rand(1, 100) <= 20;
						break;
				}
				if ($chance) {
					$convert = [
						ItemTypeIds::COAL => VanillaItems::IRON_INGOT(),
						ItemTypeIds::IRON_INGOT => VanillaItems::GOLD_INGOT(),
						ItemTypeIds::GOLD_INGOT => VanillaItems::REDSTONE_DUST(),
						ItemTypeIds::REDSTONE_DUST => VanillaItems::DIAMOND(),
						ItemTypeIds::DIAMOND => VanillaItems::EMERALD(),
						ItemTypeIds::EMERALD => VanillaBlocks::OBSIDIAN()->asItem(),
					];
					$converted = false;
					$drops = $e->getDrops();
					foreach ($drops as $key => $drop) {
						$id = $drop->getTypeId();
						if (isset($convert[$id])) {
							$convertedItem = $convert[$id];
							$drops[$key] = $convertedItem->setCount($drop->getCount());
							$converted = true;
						}
					}
					if ($converted) {
						$e->setDrops($drops);
					}
				}
			},
			ED::PURIFY => function (BlockBreakEvent $e, int $level) {
				/** @var SkyBlockPlayer $player */
				$player = $e->getPlayer();

				$chance = mt_rand(1, 10) == 1;

				if ($chance) {
					$multipliers = [
						BlockTypeIds::COBBLESTONE,
						ItemTypeIds::COAL,
						ItemTypeIds::IRON_INGOT,
						ItemTypeIds::GOLD_INGOT,
						BlockTypeIds::IRON_ORE,
						BlockTypeIds::GOLD_ORE,
						ItemTypeIds::NETHER_QUARTZ,
						ItemTypeIds::DIAMOND,
						ItemTypeIds::EMERALD
					];

					$islandLevel = $player->getGameSession()->getIslands()->getIslandAt()?->getSizeLevel() ?? 1;

					$drops = $e->getDrops();
					$price = 0;
					foreach ($drops as $key => $drop) {
						if (in_array($drop->getTypeId(), $multipliers)) {
							$value = SkyBlock::getInstance()->getShops()->getValue($drop, $islandLevel);
							if ($value !== -1) {
								$price += $value;
								unset($drops[$key]);
							}
						}
					}
					if ($price > 0) {
						$player->addTechits((int) ($price * (1 + (0.25 * $level))));
						if ($player instanceof AtPlayer && $player->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $player->playSound("break.amethyst_block", $player->getPosition()->subtract(0, 5, 0), 50, 1);
						$e->setDrops($drops);
					}
				}
			},
			ED::LUMBERJACK => function (BlockBreakEvent $e, int $level) {
				$player = $e->getPlayer();
				if (!$player->isSneaking()) return;

				$block = $e->getBlock();
				$drops = [];

				if ($block instanceof Wood) {
					$b = $block;
					$count = 0;
					while ($b instanceof Wood && $count <= 10) {
						$count++;
						$player->getWorld()->setBlock($b->getPosition(), VanillaBlocks::AIR());
						$drops[] = $b->asItem();
						$pos = $b->getPosition();
						$b = $player->getWorld()->getBlockAt($pos->getX(), $pos->getY() + 1, $pos->getZ());
					}

					if ($player instanceof AtPlayer && $player->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $player->getWorld()->addSound($player->getPosition(), new PlaySound($player->getPosition(), "random.pop"));
					$e->setDrops($drops);
				}
			},

			//Sword
			ED::KABOOM => function (EntityDamageByEntityEvent $e, int $level) {
				$chance = match ($level) {
					1 => mt_rand(1, 100) <= 2,
					2 => mt_rand(1, 100) <= 7,
					3 => mt_rand(1, 100) <= 12,
					default => mt_rand(1, 100) <= $level * 4
				};

				if ($chance) {
					$this->explosion($e->getEntity()->getPosition(), $level);

					$heartCap = 7; // 3.5 hearts

					$e->setModifier(min((($level / 4) * ($level + mt_rand(1, 5))), $heartCap), self::MODIFIER_KABOOM);
					$e->setKnockback($e->getKnockback() * 1.5);
					if (!$e->getEntity() instanceof AtPlayer) $e->setVerticalKnockBackLimit($e->getVerticalKnockBackLimit() * 1.5);
				}
			},
			ED::ZEUS => function (EntityDamageByEntityEvent $e, int $level) {
				$hurt = $e->getEntity();
				$killer = $e->getDamager();

				$chance = round(lcg_value() * 100, 2) <= $level * 4.15;
				if ($chance) {
					$this->strikeLightning($hurt->getPosition());

					$heartCap = 7; // 3.5 hearts

					$e->setModifier(min((($level / 4) * ($level + mt_rand(1, 5))), $heartCap), self::MODIFIER_ZEUS);
				}
			},
			ED::OOF => function (EntityDamageByEntityEvent $e, int $level) {
				if (mt_rand(1, 3) == 1) {
					/** @var SkyBlockPlayer $entity */
					$entity = $e->getEntity();
					foreach ($e->getEntity()->getViewers() as $viewer) {
						/** @var SkyBlockPlayer $viewer */
						if ($viewer instanceof AtPlayer && $viewer->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $viewer->playSound("random.hurt", $e->getEntity()->getPosition());
					}
					if ($entity instanceof SkyBlockPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->playSound("random.hurt");
				}
				if (mt_rand(1, 10000) === 1) {
					$entity = $e->getEntity();
					$pos = $e->getEntity()->getPosition();
					foreach ($e->getEntity()->getViewers() as $viewer) {

						/** @var SkyBlockPlayer $viewer */
						for ($i = 0; $i <= 200; $i++) $viewer->playSound("random.hurt", $pos);
						//for($i = 0; $i <= 400; $i++) $viewer->playSound("reverb.fart.long", $e->getEntity()->getPosition());
						//for($i = 0; $i <= 400; $i++) $viewer->playSound("reverb.fart", $e->getEntity()->getPosition());

						for ($i = 0; $i <= 20; $i++) {
							SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($viewer, $pos): void {
								if ($viewer->isConnected()) {
									for ($i = 0; $i <= 400; $i++) $viewer->playSound("reverb.fart.long", $pos);
									for ($i = 0; $i <= 20; $i++) $viewer->playSound("reverb.fart", $pos);
								}
							}), $i);
							if ($i % 4 === 0) {
								SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($viewer, $pos): void {
									if ($viewer->isConnected()) {
										for ($i = 0; $i <= 500; $i++) $viewer->playSound("random.hurt", $pos);
									}
								}), $i);
							}
						}
					}
					if ($entity instanceof SkyBlockPlayer) {
						for ($i = 0; $i <= 500; $i++) $entity->playSound("random.hurt");
						for ($i = 0; $i <= 400; $i++) $entity->playSound("reverb.fart.long");
						for ($i = 0; $i <= 400; $i++) $entity->playSound("reverb.fart");
					}
					$e->setBaseDamage(100000000);
					Core::announceToSS(TextFormat::BOLD . TextFormat::RED . "OOF HAS SMITED.");
				}
			},
			ED::KEY_THEFT => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 15) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 10) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 5) == 1;
						break;
				}

				/** @var SkyBlockPlayer $player */
				$player = $e->getEntity();
				/** @var SkyBlockPlayer $killer */
				$killer = $e->getDamager();
				if ($chance && $e->getFinalDamage() >= $player->getHealth()) {
					if ($player instanceof Player) {
						$stole = [
							"iron" => 0,
							"gold" => 0,
							"diamond" => 0,
							"emerald" => 0,
							"vote" => 0
						];

						for ($i = 1; $i <= $max = mt_rand(1, 3); $i++) {
							$keytype = $this->getRandomKeyType($player, $stole);
							if ($keytype !== false) {
								$player->getGameSession()->getCrates()->takeKeys($keytype, ($amt = mt_rand(1, $level)));
								$killer->getGameSession()->getCrates()->addKeys($keytype, $amt);
								$stole[$keytype]++;
							}
						}

						$count = 0;
						foreach ($stole as $type => $amount) {
							if ($amount <= 0) {
								unset($stole[$type]);
							} else {
								$count += $amount;
							}
						}

						if ($count > 0 && $killer instanceof Player) {
							$killer->sendMessage(TextFormat::AQUA . "Stole " . TextFormat::YELLOW . $count . " keys " . TextFormat::AQUA . "from " . TextFormat::RED . $player->getName() . ":");
							foreach ($stole as $type => $amount) {
								$killer->sendMessage(TextFormat::GRAY . " - " . TextFormat::GREEN . "x" . $amount . " " . $type . " keys");
							}
						}
					}
				}
			},
			ED::TIDES => function (EntityDamageByEntityEvent $e, int $level) {
				$entity = $e->getEntity();
				$chance = mt_rand(1, 100) <= $level * 6;
				if ($chance) {
					if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->addSound($entity->getPosition(), new PlaySound($entity->getPosition(), "random.splash"));
					for ($i = 0; $i < mt_rand(15, 20); $i++) {
						$entity->getWorld()->addParticle($entity->getPosition()->add(mt_rand(-10, 10) / 10, 0, mt_rand(-10, 10) / 10), new SplashParticle());
					}
					$e->setKnockback($e->getKnockback() * (1 + ($level / 4)));
					$e->setBaseDamage($e->getBaseDamage() + 1);
					if (!$entity instanceof AtPlayer) $e->setVerticalKnockBackLimit($e->getVerticalKnockBackLimit() * (1 + ($level / 4)));
				}
			},
			ED::UPLIFT => function (EntityDamageByEntityEvent $e, int $level) {
				if (mt_rand(1, 5) == 1) {
					$e->setBaseDamage($e->getBaseDamage() + 1);
					$e->setKnockback($e->getKnockback() * mt_rand(2, 3));
					if (!$e->getEntity() instanceof AtPlayer) $e->setVerticalKnockBackLimit($e->getVerticalKnockbackLimit() * mt_rand(2, 3));
				}
			},
			ED::HADES => function (EntityDamageByEntityEvent $e, int $level) {
				$chance = match ($level) {
					1 => mt_rand(1, 100) <= 5,
					2 => mt_rand(1, 100) <= 9,
					3 => mt_rand(1, 100) <= 13,
					4 => mt_rand(1, 100) <= 16,
					default => mt_rand(1, 100) <= $level * 5
				};

				if ($chance) {
					$killer = $e->getDamager();
					$entity = $e->getEntity();

					for ($i = 1; $i <= $level * 3; $i++) {
						$entity->getWorld()->addParticle($entity->getPosition()->add(mt_rand(-10, 10) * 0.1, mt_rand(0, 20) * 0.1, mt_rand(-10, 10) * 0.1), new FlameParticle());
					}

					$entity->setOnFire($level * mt_rand(1, 2));

					$e->setBaseDamage($e->getBaseDamage() + ($level * 0.5));

					if (
						$entity instanceof Living &&
						!$entity instanceof MoneyBag &&
						!$entity instanceof SupplyDrop
					) {
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * ($level + (2 * $level)), 1));
					}
				}
			},
			ED::SPITE => function (EntityDamageByEntityEvent $e, int $level) {
				$chance = match ($level) {
					1 => mt_rand(1, 100) <= 2,
					2 => mt_rand(1, 100) <= 5,
					3 => mt_rand(1, 100) <= 7,
					4 => mt_rand(1, 100) <= 10,
					default => mt_rand(1, 100) <= ($level * 3)
				};

				if ($chance) {
					$attacker = $e->getDamager();
					$victim = $e->getEntity();

					if (!($victim instanceof Living) || !($attacker instanceof Human)) return;

					$heartCap = 9; // 4.5 hearts
					$missingHealth = $attacker->getMaxHealth() - $attacker->getHealth();

					if ($victim instanceof AtPlayer && $victim->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $victim->getWorld()->addSound($victim->getPosition(), new PlaySound($victim->getPosition(), 'item.trident.throw', 100));

					for ($i = 0; $i < mt_rand(15, 20); $i++) {
						$victim->getWorld()->addParticle($victim->getPosition()->add(mt_rand(-10, 10) / 10, 0, mt_rand(-10, 10) / 10), new AngryVillagerParticle());
					}

					$itemInHand = $attacker->getInventory()->getItemInHand();

					if ($itemInHand instanceof Durable) {
						$itemInHand->applyDamage($level * (mt_rand(1, 5) !== 1 ? mt_rand(1, 3) : mt_rand(3, 5)));

						$attacker->getInventory()->setItemInHand($itemInHand);
					}

					$e->setModifier(min(max($missingHealth, $level * 2), $heartCap), self::MODIFIER_SPITE);
				}
			},
			ED::DAZE => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 15) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 10) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 7) == 1;
						break;
				}

				if ($chance) {
					$entity = $e->getEntity();
					if ($entity instanceof Living) $entity->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), 20 * ($level + (2 * $level)), $level - 1));

					if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), PlaySoundPacket::create(
						"mob.husk.death",
						$entity->getPosition()->x,
						$entity->getPosition()->y,
						$entity->getPosition()->z,
						1.0,
						1.0
					));
					$entity->getWorld()->addParticle($entity->getPosition()->add(mt_rand(-1, 1), mt_rand(-2, 2), mt_rand(-1, 1)), new PotionSplashParticle(Color::fromRGB(000000)));
				}
			},
			ED::ELECTRIFY => function (EntityDamageByEntityEvent $e, int $level) {
				$chance = mt_rand(1, 100) <= ($level == 1 ? 4 : 8);
				if ($chance) {
					SkyBlock::getInstance()->getCombat()->strikeLightning($e->getEntity()->getPosition());
					$entity = $e->getEntity();
					if ($entity instanceof Living) $entity->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), ($level == 1 ? 1 : 2) * 20, $level == 1 ? 3 : 4));

					if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), PlaySoundPacket::create(
						"conduit.attack",
						$entity->getPosition()->x,
						$entity->getPosition()->y,
						$entity->getPosition()->z,
						1.0,
						1.0
					));

					for ($i = 0; $i < 100; $i++) {
						$entity->getWorld()->addParticle($entity->getPosition()->add(mt_rand(-1, 1), mt_rand(-2, 2), mt_rand(-1, 1)), new EnchantmentTableParticle());
					}
				}
			},
			ED::GRIND => function (EntityDamageByEntityEvent $e, int $level) {
				$entity = $e->getEntity();
				if ($entity instanceof Mob) {
					if ($entity->getHealth() - $e->getFinalDamage() <= 0) {
						$damager = $e->getDamager();
						if (!$damager instanceof Player) return;
						/** @var SkyBlockPlayer $damager */
						$session = $damager->getGameSession()->getSettings();
						if ($session->getSetting(SkyBlockSettings::AUTO_XP)) {
							$damager->getXpManager()->addXp($entity->getXpDropAmount() * $level);
						} else {
							$entity->getWorld()->dropExperience($entity->getPosition(), $entity->getXpDropAmount() * $level);
						}
					}
				}
			},
			ED::LIFESTEAL => function (EntityDamageByEntityEvent $e, int $level) {
				$victim = $e->getEntity();

				$chance = match ($level) {
					1 => mt_rand(1, 20) == 1,
					2 => mt_rand(1, 13) == 1,
					3 => mt_rand(1, 10) == 1,
					default => mt_rand(1, 4) === 1
				};

				if ($chance) {
					if ($victim instanceof AtPlayer && $victim->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $victim->getWorld()->broadcastPacketToViewers($victim->getPosition(), PlaySoundPacket::create(
						"mob.phantom.bite",
						$victim->getPosition()->x,
						$victim->getPosition()->y,
						$victim->getPosition()->z,
						1.0,
						1.0
					));

					$e->setModifier($level, self::MODIFIER_LIFESTEAL);
					$e->getDamager()->setHealth($e->getDamager()->getHealth() + ($e->getFinalDamage() / 2));
				}
			},
			ED::BLEED => function (EntityDamageByEntityEvent $e, int $level) {
				$player = $e->getEntity();

				if (!$player instanceof SkyBlockPlayer) return;

				$chance = match ($level) {
					1 => mt_rand(1, 16) === 1,
					2 => mt_rand(1, 10) === 1,
					3 => mt_rand(1, 6) === 1,
					4 => mt_rand(1, 4) === 1,
					default => mt_rand(1, 3) === 1
				};

				if ($chance && $e->getDamager() instanceof Player) {
					if (mt_rand(1, 4) === 1) {
						$player->getGameSession()?->getEnchantments()->blockAbsorb(true);
					}

					$player->getWorld()->addParticle($player->getPosition(), new BlockBreakParticle(VanillaBlocks::REDSTONE()));

					/** @var SkyBlockPlayer $player */
					$player->bleed($e->getDamager(), mt_rand(30, 60) * $level);
				}
			},
			ED::RADIATION => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 20) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 15) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 8) == 1;
						break;
				}

				if ($chance) {
					$entity = $e->getEntity();
					if (
						$entity instanceof Living &&
						!$entity instanceof MoneyBag &&
						!$entity instanceof SupplyDrop
					) {
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 20 * ($level + (2 * $level)), 1));
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * ($level + (2 * $level)), $level + 1));
					}
				}
			},
			ED::PIERCE => function (EntityDamageByEntityEvent $e, int $level) {
				$entity = $e->getEntity();
				if ($entity instanceof Player) {
					foreach ($entity->getArmorInventory()->getContents(true) as $slot => $armor) {
						if ($armor instanceof Durable) {
							$damage = ($level * ((int)($armor->getMaxDurability() * 0.0015)));
							$armor->applyDamage($damage);
							$entity->getArmorInventory()->setItem($slot, $armor);
						}
					}
					if (mt_rand(1, 200) <= 15 /* 7.5% */) $e->setBaseDamage($e->getBaseDamage() + $level);
				}
			},
			ED::TECH_BLAST => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 25) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 18) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 13) == 1;
						break;
				}
				$killer = $e->getDamager();

				if ($chance) {
					//TODO: PARTICLES AND SHIT
					foreach ($killer->getWorld()->getNearbyEntities($killer->getBoundingBox()->expandedCopy(3, 3, 3)) as $entity) {
						if ($entity !== $killer && ($entity instanceof Mob || $entity instanceof Player)) {
							$dv = $entity->getPosition()->subtract($killer->getPosition()->x, $killer->getPosition()->y, $killer->getPosition()->z)->normalize();
							$entity->knockback($dv->x, $dv->z);
						}
					}
				}
			},
			ED::EXECUTE => function (EntityDamageByEntityEvent $e, int $level) {
				$entity = $e->getEntity();
				if ($entity->getHealth() <= 6) {
					$e->setBaseDamage($e->getBaseDamage() + mt_rand(0, $level));
				}
			},

			ED::DECAY => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 25) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 16) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 12) == 1;
						break;
					case 4:
						$chance = mt_rand(1, 9) == 1;
						break;
				}
				$killer = $e->getDamager();

				$entity = $e->getEntity();
				if (
					$chance &&
					$entity instanceof Living &&
					!$entity instanceof MoneyBag &&
					!$entity instanceof SupplyDrop
				) {
					$entity->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * ($level + (2 * $level)), 1));
				}
			},

			ED::STARVATION => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 30) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 25) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 15) == 1;
						break;
					case 4:
						$chance = mt_rand(1, 12) == 1;
						break;
				}
				$killer = $e->getDamager();

				if ($chance) {
					$en = $e->getEntity();
					if ($en instanceof Player && $en->getHungerManager()->getFood() > 0) {
						$en->getHungerManager()->setFood($en->getHungerManager()->getFood() - 1);
					}
				}
			},

			ED::SHUFFLE => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 20) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 18) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 13) == 1;
						break;
				}
				$killer = $e->getDamager();

				if ($chance) {
					$en = $e->getEntity();
					if ($en instanceof Player) {
						switch (mt_rand(0, 2)) {
							case 4:
								$hotbar = [];
								for ($i = 0; $i <= 8; $i++) {
									$hotbar[$i] = $en->getInventory()->getItem($i);
								}
								shuffle($hotbar);
								$hotbar = array_values($hotbar);
								foreach ($hotbar as $slot => $item) {
									$en->getInventory()->setItem($slot, $item);
								}
								break;
							default:
								$en->getInventory()->setHeldItemIndex(mt_rand(0, 8));
								break;
						}
					}
				}
			},

			ED::BACKSTAB => function (EntityDamageByEntityEvent $e, int $level) {
				$killer = $e->getDamager();
				$entity = $e->getEntity();
				if ($entity instanceof Player && !$this->isPlayerFacing($entity, $killer)) {
					$e->setBaseDamage($e->getBaseDamage() * (1 + (0.1 * $level)));
				}
			},

			//Armor
			ED::CROUCH => function (EntityDamageByEntityEvent $e, int $level) {
				$player = $e->getEntity();

				if (!$player instanceof Player || !$player->isSneaking()) return;

				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 6;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 12;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 15;
						break;
					case 4:
						$chance = mt_rand(1, 100) <= 18;
						break;
				}

				if ($chance) {
					$e->setBaseDamage($e->getBaseDamage() / (($level / 2) + 1));
				}
			},
			ED::GODLY_RETRIBUTION => function (EntityDamageByEntityEvent $e, int $level) {
				if (($pl = $e->getEntity())->getHealth() - $e->getBaseDamage() <= 5 && $pl instanceof Living && !$pl->getEffects()->has(VanillaEffects::STRENGTH())) {
					if ($pl instanceof AtPlayer && $pl->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $pl->getWorld()->addSound($pl->getPosition(), new PlaySound($pl->getPosition(), "mob.wither.ambient"));
					$pl->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 10 * 20));
					$pl->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 10 * 20, 1));
				}
			},
			ED::ENDURANCE => function (EntityDamageByEntityEvent $e, int $level) {
				$victim = $e->getEntity();

				if(!($victim instanceof SkyBlockPlayer)) return;

				$es = $victim->getGameSession()->getEnchantments();

				if(microtime(true) - $es->getLastHit() < 4.5){
					$chance = mt_rand(1, 200) <= 10; # 5%
				}elseif(microtime(true) - $es->getLastHit() < 9){
					$chance = mt_rand(1, 200) <= 5; # 2.5%
				}else{
					$chance = mt_rand(1, 200) <= 2; # 1%
				}

				if(!$chance) return;

				$damager = $e->getDamager();

				if(!($damager instanceof Living)) return;

				$vSlot = mt_rand(0, 3);

				/** @var Armor $armor */
				foreach($victim->getArmorInventory()->getContents(true) as $index => $armor){
					if($index !== $vSlot) continue;
					if($armor->isNull()) continue;
					if(!$armor instanceof Durable || $armor->getDamage() <= 0) continue;

					$damageReduced = $armor->getDamage() - ($level * ((int)($armor->getMaxDurability() * 0.04)));

					$armor->setDamage(($damageReduced <= 0 ? 0 : $damageReduced));
					$victim->getArmorInventory()->setItem($index, $armor);

					
					$dSlot = mt_rand(0, 3);

					/** @var Armor $dmgPiece */
					foreach($damager->getArmorInventory()->getContents(true) as $slot => $dmgPiece){
						if($slot !== $dSlot) continue;
						if($dmgPiece->isNull()) continue;
						if(!$dmgPiece instanceof Durable || $dmgPiece->getDamage() <= 0) continue;

						$dmgPiece->applyDamage(($level * ((int)($dmgPiece->getMaxDurability() * 0.04))));
						$damager->getArmorInventory()->setItem($slot, $dmgPiece);
						break;
					}

					if($victim instanceof AtPlayer && $victim->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $victim->getWorld()->addSound($victim->getPosition(), new GenericSound($victim->getPosition(), LevelSoundEvent::RANDOM_ANVIL_USE));
					break;
				}
			},
			ED::SNARE => function (EntityDamageByEntityEvent $e, int $level) {
				$killer = $e->getDamager();
				$chance = mt_rand(1, 100) <= 7;
				if ($chance && $e->getEntity() instanceof Player) {
					$this->drag($e->getEntity(), $killer);
					if ($killer instanceof AtPlayer && $killer->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $killer->getWorld()->addSound($killer->getPosition(), new LaunchSound(), $killer->getViewers());
				}
			},
			ED::RAGE => function (EntityDamageByEntityEvent $e, int $level) {
				$chance = mt_rand(1, 100) <= ($level * 5);
				$entity = $e->getEntity();
				if ($chance && $entity instanceof Living) {
					$entity->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * ($level * mt_rand(1, 2))));
					$entity->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * ($level * mt_rand(1, 2))));

					if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), PlaySoundPacket::create(
						"bloom.sculk_catalyst",
						$entity->getPosition()->x,
						$entity->getPosition()->y,
						$entity->getPosition()->z,
						1.0,
						1.0
					));
				}
			},
			ED::SORCERY => function (EntityDamageByEntityEvent $e, int $level) {
				$killer = $e->getDamager();
				$entity = $e->getEntity();
				$chance = mt_rand(1, 100) <= ($level == 1 ? 4 : ($level == 2 ? 9 : 12));
				if ($chance && $killer instanceof Living) {
					$bad = [
						VanillaEffects::SLOWNESS(),
						VanillaEffects::MINING_FATIGUE(),
						VanillaEffects::NAUSEA(),
						VanillaEffects::BLINDNESS(),
						VanillaEffects::HUNGER(),
						VanillaEffects::WEAKNESS(),
						VanillaEffects::POISON(),
						VanillaEffects::FATAL_POISON(),
						VanillaEffects::WITHER(),
					];
					$effect = new EffectInstance($bad[array_rand($bad)], 20 * ($level * 4));
					$killer->getEffects()->add($effect);
					if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->addSound($entity->getPosition(), new PlaySound($entity->getPosition(), "mob.evocation_illager.cast_spell"));
				}
			},
			ED::BLESSING => function (EntityDamageByEntityEvent $e, int $level) {
				$killer = $e->getDamager();
				$chance = mt_rand(1, 100) <= ($level == 1 ? 3 : ($level == 2 ? 6 : 9));
				$entity = $e->getEntity();
				if ($chance && $killer instanceof Living && $entity instanceof Living) {
					$bad = [
						EffectIdMap::getInstance()->toId(VanillaEffects::SLOWNESS()),
						EffectIdMap::getInstance()->toId(VanillaEffects::MINING_FATIGUE()),
						EffectIdMap::getInstance()->toId(VanillaEffects::NAUSEA()),
						EffectIdMap::getInstance()->toId(VanillaEffects::BLINDNESS()),
						EffectIdMap::getInstance()->toId(VanillaEffects::HUNGER()),
						EffectIdMap::getInstance()->toId(VanillaEffects::WEAKNESS()),
						EffectIdMap::getInstance()->toId(VanillaEffects::POISON()),
						EffectIdMap::getInstance()->toId(VanillaEffects::FATAL_POISON()),
						EffectIdMap::getInstance()->toId(VanillaEffects::WITHER()),
					];
					foreach ($killer->getEffects()->all() as $effect) {
						if (in_array(EffectIdMap::getInstance()->toId($effect->getType()), $bad)) {
							$entity->getEffects()->remove($effect->getType());
							$killer->getEffects()->add($effect);
						}
					}

					if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), PlaySoundPacket::create(
						"beacon.power",
						$entity->getPosition()->x,
						$entity->getPosition()->y,
						$entity->getPosition()->z,
						1.0,
						1.0
					));
				}
			},
			ED::FIREBALL => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 20) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 16) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 12) == 1;
						break;
					case 4:
						$chance = mt_rand(1, 9) == 1;
						break;
				}

				if ($chance) {
					$player = $e->getEntity();
					foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy(3, 3, 3)) as $entity) {
						if ($entity !== $player && ($entity instanceof Mob || $entity instanceof Player)) {
							$entity->setOnFire($level + mt_rand(1, 2));
							if ($entity instanceof SkyBlockPlayer) {
								/** @var SkyBlockPlayer $entity */
								$entity->getGameSession()?->getCombat()->getCombatMode()?->setHit($player);
							}
						}
					}
				}
			},
			ED::OBSIDIAN => function (EntityDamageByEntityEvent $e, int $level) {
				$damageReduced = $e->getBaseDamage() * ($level === 1 ? 0.05 : 0.10);

				$e->setBaseDamage($e->getBaseDamage() - $damageReduced);
			},
			ED::ANTI_KNOCKBACK => function (EntityDamageByEntityEvent $e, int $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 12) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 9) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 6) == 1;
						break;
				}

				if ($chance) {
					$e->setKnockback(0);
					$e->setVerticalKnockBackLimit(0);
				}
			},
			ED::ABSORB => function (EntityDamageByEntityEvent $e, int $level) {
				$victim = $e->getEntity();

				if (!$victim instanceof SkyBlockPlayer) return;

				$es = $victim->getGameSession()?->getEnchantments();

				if ($es === null) return;

				if ($es->isAbsorbing()) return;
				if (mt_rand(1, 15) !== 1) return;

				for ($i = 0; $i < 3; $i++) {
					SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($victim) {
						if (!($victim->isOnline())) return;

						if ($victim instanceof AtPlayer && $victim->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $victim->getWorld()->broadcastPacketToViewers($victim->getPosition(), PlaySoundPacket::create(
							"random.drink",
							$victim->getPosition()->x,
							$victim->getPosition()->y,
							$victim->getPosition()->z,
							1.0,
							1.0
						));
					}), $i * 5);
				}

				for ($i = 0; $i < 100; $i++) {
					$victim->getWorld()->addParticle($victim->getPosition()->add(mt_rand(-1, 1), mt_rand(-2, 2), mt_rand(-1, 1)), new PortalParticle());
				}

				$es->absorb($victim);
			},
			ED::FORESIGHT => function (EntityDamageByEntityEvent $e, int $level) {
				$victim = $e->getEntity();

				if (!$victim instanceof SkyBlockPlayer) return;

				$es = $victim->getGameSession()?->getEnchantments();

				if (is_null($es)) return;

				if ($es->isForeseeing()) {
					$es->addForeseenHits(1);

					if ($es->getHitsForeseen() >= 3) {
						$es->setForeseenHits(0);
						$es->canForesee(false);
					}

					$damageReduced = match ($level) {
						1 => 0.025,
						2 => 0.05,
						default => 0.10
					};

					$e->setBaseDamage($e->getBaseDamage() - ($e->getBaseDamage() * $damageReduced));
					return;
				}

				if (mt_rand(1, 100) > 8) return;

				$es->canForesee(true);

				if ($victim instanceof AtPlayer && $victim->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $victim->getWorld()->broadcastPacketToViewers($victim->getPosition(), PlaySoundPacket::create(
					"mob.allay.idle",
					$victim->getPosition()->x,
					$victim->getPosition()->y,
					$victim->getPosition()->z,
					1.0,
					1.0
				));
			},
			ED::FERTILIZE => function(BlockBreakEvent $e, int $level){
				$player = $e->getPlayer();

				if(!$player instanceof SkyBlockPlayer) return;

				$block = $e->getBlock();

				if(!(
					$block instanceof Crops ||
					$block instanceof NetherWartPlant
				)) return;

				$seed = match(true){
					$block instanceof Wheat => VanillaItems::WHEAT_SEEDS(),
					$block instanceof MelonStem => VanillaItems::MELON_SEEDS(),
					$block instanceof PumpkinStem => VanillaItems::PUMPKIN_SEEDS(),
					default => $block->asItem()
				};

				if(($first = $player->getInventory()->first($seed)) == -1) return;

				$seed = $player->getInventory()->getItem($first);

				$player->getInventory()->setItem($first, $seed->setCount($seed->getCount() - 1));

				if(
					$block->getAge() === Crops::MAX_AGE ||
					$block->getAge() === NetherWartPlant::MAX_AGE
				){
					$maxAge = ($block instanceof NetherWartPlant ? NetherWartPlant::MAX_AGE : Crops::MAX_AGE);

					$ageChance = mt_rand(1, 100);
					if($block instanceof NetherWartPlant){
						$age = match($level){
							1 => ($ageChance <= 80 ? 0 : ($ageChance <= 95 ? 1 : 2)),
							2 => ($ageChance <= 60 ? 0 : ($ageChance <= 80 ? 1 : 2)),
							3 => ($ageChance <= 30 ? 0 : ($ageChance <= 60 ? mt_rand(1, 2) : $maxAge)),
							default => 0
						};
					}else{
						$age = match($level){
							1 => ($ageChance <= 80 ? 0 : ($ageChance <= 95 ? mt_rand(1, 2) : mt_rand(3, $maxAge))),
							2 => ($ageChance <= 60 ? 0 : ($ageChance <= 80 ? mt_rand(1, 3) : mt_rand(4, $maxAge))),
							3 => ($ageChance <= 30 ? 0 : ($ageChance <= 60 ? mt_rand(1, 4) : mt_rand(5, $maxAge))),
							default => 0
						};
					}

					$block->setAge($age);
				}else{
					$block->setAge(0);
				}

				SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player, $block){
					$player->getWorld()->setBlock($block->getPosition(), $block);

					for($i = 0; $i < mt_rand(25, 35); $i++){
						$sound = PlaySoundPacket::create(
							"random.bow",
							$block->getPosition()->x,
							$block->getPosition()->y,
							$block->getPosition()->z,
							0.50,
							1.0
						);
						$player->getWorld()->broadcastPacketToViewers($player->getPosition(), $sound);
						$player->getWorld()->addParticle($block->getPosition()->add(mt_rand(-20, 20) / 20, mt_rand(-20, 20) / 20, mt_rand(-20, 20) / 20), new HappyVillagerParticle());
					}
				}), 20);
			},
			ED::WORM => function(BlockBreakEvent $e, int $level){
				$player = $e->getPlayer();

				if(!$player instanceof SkyBlockPlayer) return;

				$item = $e->getItem();
				$block = $e->getBlock();

				if(!(
					$block instanceof Crops ||
					$block instanceof Melon ||
					$block instanceof Pumpkin ||
					$block instanceof NetherWartPlant
				)) return;

				$face = $player->getHorizontalFacing();
				$drops = $e->getDrops();

				for($i = 1; $i < $level; $i++){
					$pos = $block->getPosition()->getSide($face, $i);
					$found = $pos->getWorld()->getBlock($pos);
	
					if(!(
						$found instanceof Crops && !$found instanceof Stem ||
						$found instanceof Melon ||
						$found instanceof Pumpkin ||
						$found instanceof NetherWartPlant
					)) return;

					foreach($found->getDrops($item) as $drop){
						$drops[] = $drop;
					}

					$pos->getWorld()->setBlock($pos, VanillaBlocks::AIR());
					$pos->getWorld()->addParticle($pos, new BlockBreakParticle($block));
					$pos->getWorld()->addSound($pos, new BlockBreakSound($block));
				}

				$e->setDrops($drops);
			},
			ED::HARVEST => function(BlockBreakEvent $e, int $level){
				$player = $e->getPlayer();

				if(!$player instanceof SkyBlockPlayer) return;

				$item = $e->getItem();
				$block = $e->getBlock();

				if(!(
					$block instanceof Crops ||
					$block instanceof NetherWartPlant
				)) return;

				if($level == 2){
					$positions = [
						[1, 1],
						[0, 1],
						[1, 0],
						[-1, -1,],
						[0, -1],
						[-1, 0],
						[0, 0],
						[-1, 1],
						[1, -1]
					];
				}else{
					$positions = [
						[1, 0],
						[0, 1],
						[0, 0],
						[1, 1]
					];
				}

				$drops = $e->getDrops();

				foreach($positions as [$x, $z]){
					$found = $player->getWorld()->getBlock($block->getPosition()->add($x, 0, $z));

					if(!($found instanceof Crops || $found instanceof NetherWartPlant)) continue;

					foreach($found->getDrops($item) as $drop){
						$drops[] = $drop;
					}

					$player->getWorld()->setBlock($found->getPosition(), VanillaBlocks::AIR());
					$player->getWorld()->addParticle($found->getPosition(), new BlockBreakParticle($found));
					$player->getWorld()->addSound($found->getPosition(), new BlockBreakSound($found));

					if(
						!is_null(($ench = $item->getEnchantment(EnchantmentRegistry::CAPSULE()->getEnchantment()))) &&
						$block->getPosition()->distance($found->getPosition()) >= 1
					){
						$this->event[ED::CAPSULE](new BlockBreakEvent(
							$player, 
							$found, 
							$item, 
							$e->getInstaBreak(), 
							$e->getDrops(), 
							$found->getXpDropForTool($item)
						), $ench->getLevel());
					}

					if(
						!is_null(($ench = $item->getEnchantment(EnchantmentRegistry::BURROW()->getEnchantment()))) &&
						$block->getPosition()->distance($found->getPosition()) >= 1
					){
						$this->event[ED::BURROW](new BlockBreakEvent(
							$player, 
							$found, 
							$item, 
							$e->getInstaBreak(), 
							$e->getDrops(), 
							$found->getXpDropForTool($item)
						), $ench->getLevel());
					}

					if(
						!is_null(($ench = $item->getEnchantment(EnchantmentRegistry::FERTILIZE()->getEnchantment()))) &&
						$block->getPosition()->distance($found->getPosition()) >= 1
					){
						$this->event[ED::FERTILIZE](new BlockBreakEvent(
							$player, 
							$found, 
							$item, 
							$e->getInstaBreak(), 
							$e->getDrops(), 
							$found->getXpDropForTool($item)
						), $ench->getLevel());
					}
				}

				$e->setDrops($drops);
			},
			ED::DEBARKER => function(PlayerInteractEvent $e, int $level){
				$player = $e->getPlayer();

				if(!$player->isSneaking()) return;
				if($e->getAction() != PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;

				$block = $e->getBlock();

				if($block instanceof Wood){
					$b = $block;
					$count = 0;
					while($b instanceof Wood && $count <= 10){
						$pos = $b->getPosition();

						$count++;

						$pos->getWorld()->addParticle($pos, new BlockBreakParticle($b));
						$pos->getWorld()->setBlock($pos, $b->setStripped(true));

						$b = $player->getWorld()->getBlockAt($pos->getX(), $pos->getY() + 1, $pos->getZ());
					}

					if($player instanceof AtPlayer && $player->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $player->getWorld()->addSound($player->getPosition(), new PlaySound($player->getPosition(), "random.pop"));
				}
			},
			ED::VENDOR => function(BlockBreakEvent $e, int $level){
				$player = $e->getPlayer();

				if(!(
					$player instanceof SkyBlockPlayer && 
					$player->isLoaded()
				)) return;
				if(!$player->isSneaking()) return;

				$isession = $player->getGameSession()->getIslands();
				$island = $isession->getIslandAt() ?? $isession->getLastIslandAt();

				if(is_null($island)) return;

				$block = $e->getBlock();

				if(!(
					$block instanceof Crops || $block instanceof Bamboo ||
					$block instanceof Melon || $block instanceof Pumpkin || 
					$block instanceof Sugarcane || $block instanceof Cactus || 
					$block instanceof RedMushroomBlock || $block instanceof NetherWartPlant || 
					$block instanceof ChorusPlant
				)) return;

				$price = -1;

				$drops = [];

				foreach($block->getDrops($e->getItem()) as $drop){
					$value = SkyBlock::getInstance()->getShops()->getValue($drop, $island->getSizeLevel(), $player);

					if($value > -1){
						$price += $value;
					}else{
						$drops[] = $drop;
					}
				}

				if($price > 0) $player->addTechits($price);

				$e->setDrops($drops);
			},
			ED::CAPSULE => function(BlockBreakEvent $e, int $level){
				$player = $e->getPlayer();

				if(!$player instanceof SkyBlockPlayer) return;

				$block = $e->getBlock();

				if(!(
					$block instanceof Crops || $block instanceof Bamboo ||
					$block instanceof Melon || $block instanceof Pumpkin || 
					$block instanceof Sugarcane || $block instanceof Cactus || 
					$block instanceof RedMushroomBlock || $block instanceof NetherWartPlant || 
					$block instanceof ChorusPlant
				)) return;

				$chance = match($level){
					1 => round(lcg_value() * 100, 2) <= 3.5,
					2 => round(lcg_value() * 100, 2) <= 6.5,
					3 => round(lcg_value() * 100, 2) <= 9.5,
					default => round(lcg_value() * 100, 2) <= 3 * $level,
				};

				if(
					$block instanceof Sugarcane ||
					$block instanceof Crops ||
					$block instanceof NetherWartPlant
				){
					$chance = match($level){
						1 => round(lcg_value() * 100, 2) <= 0.75,
						2 => round(lcg_value() * 100, 2) <= 1.50,
						3 => round(lcg_value() * 100, 2) <= 2.25,
						default => round(lcg_value() * 100, 2) <= 0.75 * $level,
					};
				}

				if($chance){
					$reward = $this->getRandomCapsuleItem($level);
					$giveItem = true;

					if($reward instanceof TechitNote){
						// Can't do getInventory()->first() & get item from slot because all techit notes have different ids
						foreach($player->getInventory()->getContents(true) as $slot => $item){
							if(!$item instanceof TechitNote) continue;
							if (!$item->getCreatedBy() === "CAPSULE" . EnchantmentUtils::getRoman($level)) continue;

							$item->setup("CAPSULE" . EnchantmentUtils::getRoman($level), $item->getTechits() + (1500 * $level));
							$player->getInventory()->setItem($slot, $item);
							$giveItem = false;
							break;
						}
					}

					if($giveItem){
						$player->getInventory()->addItem($reward);
					}

					for($i = 0; $i < mt_rand(25, 30); $i++){
						$player->getWorld()->addSound($player->getPosition(), new PlaySound($player->getPosition(), "block.chain.place"));
						$player->getWorld()->addParticle($block->getPosition()->add(mt_rand(-20, 20) / 20, mt_rand(-20, 20) / 20, mt_rand(-20, 20) / 20), new DustParticle(new Color(242, 183, 90, 1)));
					}

				}
			},
			ED::BURROW => function(BlockBreakEvent $e, int $level){
				$player = $e->getPlayer();

				if(!$player instanceof SkyBlockPlayer) return;

				$block = $e->getBlock();

				if(!(
					$block instanceof Crops || $block instanceof Bamboo ||
					$block instanceof Melon || $block instanceof Pumpkin || 
					$block instanceof Sugarcane || $block instanceof Cactus || 
					$block instanceof RedMushroomBlock || $block instanceof NetherWartPlant || 
					$block instanceof ChorusPlant
				)) return;

				$chance = match($level){
					1 => round(lcg_value() * 100, 2) <= 3.5,
					2 => round(lcg_value() * 100, 2) <= 6.5,
					3 => round(lcg_value() * 100, 2) <= 9.5,
					default => round(lcg_value() * 100, 2) <= 3 * $level,
				};

				if(
					$block instanceof Sugarcane ||
					$block instanceof Crops ||
					$block instanceof NetherWartPlant
				){
					$chance = match($level){
						1 => round(lcg_value() * 100, 2) <= 0.25,
						2 => round(lcg_value() * 100, 2) <= 0.50,
						3 => round(lcg_value() * 100, 2) <= 0.75,
						default => round(lcg_value() * 100, 2) <= 0.25 * $level,
					};
				}

				if($chance){
					$type = null;
					$ktr = round(lcg_value() * 100, 3);

					switch (true) {
						case $ktr <= 0.0025:
							$type = "divine";
							break;
						case $ktr <= 4.25:
							$type = "emerald";
							break;
						case $ktr <= 9.5:
							$type = "diamond";
							break;
						case $ktr <= 45.5:
							$type = "gold";
							break;
						case $ktr <= 75.5:
							$type = "iron";
							break;
						default:
							return false;
					}

					$event = new KeyFindEvent($player, $type, 1);
					$event->call();

					if($event->isCancelled()) return false;

					$player->getGameSession()->getCrates()->addKeys($type, $event->getAmount());
					$player->playSound("mob.chicken.hurt");
					$player->sendTitle(
						TextFormat::YELLOW . Crates::FIND_WORDS[array_rand(Crates::FIND_WORDS)], 
						TextFormat::YELLOW . "Found x" . $event->getAmount() . " " . Crates::KEY_COLORS[$type] . ucfirst($type) . " Key", 
						10, 
						40, 
						10
					);
				}
			},
			ED::TILLER => function(PlayerInteractEvent $e, int $level){
				$player = $e->getPlayer();

				if(!(
					$player instanceof SkyBlockPlayer &&
					$player->isSneaking()
				)) return;

				if($e->getAction() != PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;

				$block = $e->getBlock();

				if(!(
					$block instanceof Dirt ||
					$block instanceof Grass
				)) return;

				$pos = $block->getPosition();
				$bb = new AxisAlignedBB(
					$pos->x,
					$pos->y,
					$pos->z,
					$pos->x,
					$pos->y,
					$pos->z
				);
				$bb->expand($level, 0, $level);

				for($x = $bb->minX; $x <= $bb->maxX; $x++){
					for($z = $bb->minZ; $z <= $bb->maxZ; $z++){
						$ground = $pos->getWorld()->getBlockAt($x, $pos->y, $z);

						if(!(
							$ground instanceof Dirt ||
							$ground instanceof Grass
						)) continue;

						$pos->getWorld()->setBlock($ground->getPosition(), VanillaBlocks::FARMLAND()->setWetness(($level === 4 ? Farmland::MAX_WETNESS : $level - 1)));
						$pos->getWorld()->addParticle($ground->getPosition()->add(mt_rand(-20, 20) / 20, (mt_rand(-20, 20) / 20) + 1, mt_rand(-20, 20) / 20), new BlockBreakParticle($ground));
					}
				}
			}
		];

		//Armor stuff
		$this->equip = [
			ED::OVERLORD => function (Living $player, $beforelevel, $afterlevel) {
				$player->setMaxHealth(20 + ($afterlevel * 2));

				if ($player->getHealth() >= 20 + ($beforelevel * 2)) {
					$player->setHealth($player->getMaxHealth());
				}
			},

			ED::GLOWING => function (Living $player, $beforelevel, $afterlevel) {
				$player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 20 * 99999, 0, false));
			},

			ED::GEARS => function (Living $player, $beforelevel, $afterlevel) {
				if (!$player instanceof SkyBlockPlayer || !$player->getGameSession()->getParkour()->hasCourseAttempt())
					$player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 99999, $afterlevel - 1, false));
			},
			ED::BUNNY => function (Living $player, $beforelevel, $afterlevel) {
				if (!$player instanceof SkyBlockPlayer || !$player->getGameSession()->getParkour()->hasCourseAttempt())
					$player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 99999, $afterlevel - 1, false));
			},
		];
		$this->unequip = [
			ED::OVERLORD => function (Living $player, $beforelevel, $afterlevel) {
				$player->setMaxHealth(20 + ($afterlevel * 2));
			},

			ED::GLOWING => function (Living $player, $beforelevel, $afterlevel) {
				$player->getEffects()->remove(VanillaEffects::NIGHT_VISION());
			},

			ED::GEARS => function (Living $player, $beforelevel, $afterlevel) {
				$player->getEffects()->remove(VanillaEffects::SPEED());
			},
			ED::BUNNY => function (Living $player, $beforelevel, $afterlevel) {
				$player->getEffects()->remove(VanillaEffects::JUMP_BOOST());
			},
		];

		$this->task = [
			ED::GLOWING => function (Living $player, $currentTick, $level) {
				if (!$player->getEffects()->has(VanillaEffects::NIGHT_VISION())) $player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 20 * 99999, 0, false));
			},

			ED::GEARS => function (Living $player, $currentTick, $level) {
				if (
					!$player->getEffects()->has(VanillaEffects::SPEED()) && (!$player instanceof SkyBlockPlayer || ($player->isLoaded() && !$player->getGameSession()->getParkour()->hasCourseAttempt()))
				) $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 99999, $level - 1, false));
			},
			ED::BUNNY => function (Living $player, $currentTick, $level) {
				if (
					!$player->getEffects()->has(VanillaEffects::JUMP_BOOST()) && (!$player instanceof SkyBlockPlayer || ($player->isLoaded() && !$player->getGameSession()->getParkour()->hasCourseAttempt()))
				) $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 99999, $level - 1, false));
			},
		];
	}

	public function isPlayerFacing(Player $player1, Player $player2, float $tolerance = 0.35): bool {
		$direction1 = $player1->getDirectionVector();
		$direction2 = $player2->getDirectionVector();

		// Normalize the direction vectors
		$direction1->normalize();
		$direction2->normalize();

		// Calculate the dot product of the direction vectors
		$dotProduct = $direction1->dot($direction2);

		// Check if the dot product is within the tolerance range
		return $dotProduct >= -1 - $tolerance && $dotProduct <= -1 + $tolerance;
	}

	public function explosion(Position $pos, int $size = 2) {
		$pos->getWorld()->addParticle($pos, new HugeExplodeParticle());
		foreach ($pos->getWorld()->getPlayers() as $player) {
			if ($player instanceof AtPlayer && $player->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::create(LevelSoundEvent::EXPLODE, $pos, -1, ":", false, false, -1));
		}
	}

	public function strikeLightning(Position $pos): void {
		$pos->getWorld()->addSound($pos, new PlaySound($pos, "ambient.weather.lightning.impact"));
		$pk = new AddActorPacket();
		$pk->type = "minecraft:lightning_bolt";
		$pk->actorRuntimeId = $pk->actorUniqueId = $eid = Entity::nextRuntimeId();
		$pk->position = $pos->asVector3();
		$pk->yaw = $pk->pitch = 0;
		$pk->syncedProperties = new PropertySyncData([], []);

		$p2d = [];
		foreach ($pos->getWorld()->getPlayers() as $p) {
			/** @var SkyBlockPlayer $p */
			if ($p->isLoaded() && $p->getGameSession()->getSettings()->getSetting(SkyBlockSettings::LIGHTNING)) {
				$p->getNetworkSession()->sendDataPacket($pk);
				$p2d[] = $p;
			}
		}
		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($p2d, $eid): void {
			$pk = new RemoveActorPacket();
			$pk->actorUniqueId = $eid;
			foreach ($p2d as $p) if ($p->isConnected()) $p->getNetworkSession()->sendDataPacket($pk);
		}), 20);
	}

	public function getRandomKeyType(Player $player, array $takingalready = [], int $tries = 0) {
		/** @var SkyBlockPlayer $player */
		if ($tries >= 10) return false;
		$type = ["iron", "gold", "diamond", "emerald", "vote"][(
			0 + 									// iron
			(1 * floor(mt_rand(80, 140) / 100)) +	// gold
			(1 * floor(mt_rand(50, 130) / 100)) +	// diamond
			(1 * floor(mt_rand(15, 120) / 100)) +	// emerald
			(1 * floor(mt_rand(0, 110) / 100))		// vote
		)];
		if (mt_rand(1, 100) <= 40) $type = "iron";
		$amt = $player->getGameSession()->getCrates()->getKeys($type);
		if (($amt - $takingalready[$type]) <= 0 && $tries < 10) {
			$tries++;
			$type = $this->getRandomKeyType($player, $takingalready, $tries);
		}
		return $type;
	}

	public function getRandomCapsuleItem(int $level = 1) : Item{
		$items = [
			VanillaItems::GOLDEN_APPLE()->setCount(mt_rand(1, $level == 1 ? mt_rand(2, 3) : mt_rand(2, 6))),
			VanillaItems::EXPERIENCE_BOTTLE()->setCount(mt_rand(1, $level == 1 ? mt_rand(3, 8) : mt_rand(3, 16))),
		];
		$rare = [
			ItemRegistry::NAMETAG()->init(),
			ItemRegistry::CUSTOM_DEATH_TAG()->init(),
			ItemRegistry::TECHIT_NOTE()->setup("CAPSULE" . EnchantmentUtils::getRoman($level), 250 * $level),
			ItemRegistry::GEN_BOOSTER()->setup((50 * $level)),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(ED::RARITY_COMMON)->init(),
			ItemRegistry::SELL_WAND()->init()
		];
		$very_rare = [
			ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount(mt_rand(1, $level == 1 ? 1 : mt_rand(2, 3))),
			ItemRegistry::PET_KEY()->init(),
			BlockRegistry::PET_BOX()->addData(BlockRegistry::PET_BOX()->asItem()),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(ED::RARITY_UNCOMMON)->init(),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(ED::RARITY_RARE)->init(),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(ED::RARITY_LEGENDARY)->init()
		];

		if($level >= 2){
			if(mt_rand(1, 100) <= 35){
				foreach($rare as $i) $items[] = $i;
			}

			if($level >= 3){
				if(round(lcg_value() * 100, 5) <= 0.00985){
					foreach($very_rare as $i){
						if(!(
							$i instanceof ItemBlock && 
							$i->getBlock() instanceof PetBox &&
							mt_rand(1, 100) <= 45
						)) continue;

						$items[] = $i;
					}
				}
			}
		}

		return $items[array_rand($items)];
	}

	public function drag(Player $to, Entity $from): void {
		if (!$from instanceof Living) return;
		$t = $from->getPosition()->asVector3();
		$dv = $to->getPosition()->asVector3()->subtract($t->x, $t->y, $t->z)->normalize();
		$from->knockBack($dv->x * 1.5, $dv->z * 1.5, 0.5, 0.15);
	}
}

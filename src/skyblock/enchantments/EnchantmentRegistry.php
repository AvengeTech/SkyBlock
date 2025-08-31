<?php

declare(strict_types=1);

namespace skyblock\enchantments;

use skyblock\enchantments\EnchantmentData as ED;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;
use skyblock\enchantments\type\Enchantment;
use skyblock\enchantments\type\armor\{
	boots\BunnyEnchantment,
	boots\GearsEnchantment,
	AbsorbEnchantment,
	AntiKnockbackEnchantment,
	BlessingEnchantment,
	leggings\CrouchEnchantment,
	DodgeEnchantment,
	EnduranceEnchantment,
	FireballEnchantment,
	helmet\ForesightEnchantment,
	helmet\GlowingEnchantment,
	GodlyRetributionEnchantment,
	ObsidianEnchantment,
	OverlordEnchantment,
	chestplate\RageEnchantment,
	SnareEnchantment,
	SorceryEnchantment,
};
use skyblock\enchantments\type\tool\{
	pickaxe\AutoSmeltEnchantment,
	pickaxe\FeatherWeightEnchantment,
	pickaxe\FeedEnchantment,
	pickaxe\KeyMagnetEnchantment,
	pickaxe\KeyRushEnchantment,
	pickaxe\MagnifyEnchantment,
	pickaxe\PurifyEnchantment,
	pickaxe\SiftEnchantment,
	pickaxe\TransfusionEnchantment,
	pickaxe\TransmutationEnchantment,

	sword\BackstabEnchantment,
	sword\BleedEnchantment,
	sword\ComboEnchantment,
	sword\DazeEnchantment,
	sword\DecayEnchantment,
	sword\ElectrifyEnchantment,
	sword\ExecuteEnchantment,
	sword\GrindEnchantment,
	sword\HadesEnchantment,
	sword\KaboomEnchantment,
	sword\KeyTheftEnchantment,
	sword\LifestealEnchantment,
	sword\OofEnchantment,
	sword\PierceEnchantment,
	sword\RadiationEnchantment,
	sword\ShuffleEnchantment,
	sword\SpiteEnchantment,
	sword\StarvationEnchantment,
	sword\TidesEnchantment,
	sword\TechBlastEnchantment,
	sword\UpliftEnchantment,
	sword\ZeusEnchantment,

	rod\FlingEnchantment,
	rod\MetalDetectorEnchantment,
	rod\PoseidonEnchantment,
	rod\SuperGlueEnchantment,
	rod\SonarEnchantment,
	rod\ThermalHookEnchantment,

	axe\DebarkerEnchantment,
	axe\LumberjackEnchantment,
	axe\WormEnchantment,

	hoe\BurrowEnchantment,
	hoe\CapsuleEnchantment,
	hoe\FertilizeEnchantment,
	hoe\HarvestEnchantment,
	hoe\TillerEnchantment,
	hoe\VendorEnchantment,
};

/**
 * Vanilla
 * @method static Enchantment PROTECTION()
 * @method static Enchantment FIRE_PROTECTION()
 * @method static Enchantment FEATHER_FALLING()
 * @method static Enchantment BLAST_PROTECTION()
 * @method static Enchantment PROJECTILE_PROTECTION()
 * @method static Enchantment THORNS()
 * @method static Enchantment RESPIRATION()
 * @method static Enchantment DEPTH_STRIDER()
 * @method static Enchantment AQUA_AFFINITY()
 * @method static Enchantment SHARPNESS()
 * @method static Enchantment SMITE()
 * @method static Enchantment BANE_OF_ARTHROPODS()
 * @method static Enchantment KNOCKBACK()
 * @method static Enchantment FIRE_ASPECT()
 * @method static Enchantment LOOTING()
 * @method static Enchantment EFFICIENCY()
 * @method static Enchantment SILK_TOUCH()
 * @method static Enchantment UNBREAKING()
 * @method static Enchantment FORTUNE()
 * @method static Enchantment POWER()
 * @method static Enchantment PUNCH()
 * @method static Enchantment FLAME()
 * @method static Enchantment INFINITY()
 * @method static Enchantment LUCK_OF_THE_SEA()
 * @method static Enchantment LURE()
 * @method static Enchantment FROST_WALKER()
 * @method static Enchantment MENDING()
 * @method static Enchantment BINDING()
 * @method static Enchantment VANISHING()
 * @method static Enchantment IMPALING()
 * @method static Enchantment RIPTIDE()
 * @method static Enchantment LOYALTY()
 * @method static Enchantment CHANNELING()
 * 
 * Pickaxe
 * @method static AutoSmeltEnchantment AUTOSMELT()
 * @method static FeatherWeightEnchantment FEATHER_WEIGHT()
 * @method static FeedEnchantment FEED()
 * @method static KeyMagnetEnchantment KEY_MAGNET()
 * @method static KeyRushEnchantment KEY_RUSH()
 * @method static MagnifyEnchantment MAGNIFY()
 * @method static PurifyEnchantment PURIFY()
 * @method static SiftEnchantment SIFT()
 * @method static TransfusionEnchantment TRANSFUSION()
 * @method static TransmutationEnchantment TRANSMUTATION()
 * 
 * Sword
 * @method static BackstabEnchantment BACKSTAB()
 * @method static BleedEnchantment BLEED()
 * @method static ComboEnchantment COMBO()
 * @method static DazeEnchantment DAZE()
 * @method static DecayEnchantment DECAY()
 * @method static ElectrifyEnchantment ELECTRIFY()
 * @method static ExecuteEnchantment EXECUTE()
 * @method static GrindEnchantment GRIND()
 * @method static HadesEnchantment HADES()
 * @method static KaboomEnchantment KABOOM()
 * @method static KeyTheftEnchantment KEY_THEFT()
 * @method static LifestealEnchantment LIFESTEAL()
 * @method static OofEnchantment OOF()
 * @method static PierceEnchantment PIERCE()
 * @method static RadiationEnchantment RADIATION()
 * @method static ShuffleEnchantment SHUFFLE()
 * @method static SpiteEnchantment SPITE()
 * @method static StarvationEnchantment STARVATION()
 * @method static TidesEnchantment TIDES()
 * @method static TechBlastEnchantment TECH_BLAST()
 * @method static UpliftEnchantment UPLIFT()
 * @method static ZeusEnchantment ZEUS()
 * 
 * Fishing Rod
 * @method static FlingEnchantment FLING()
 * @method static MetalDetectorEnchantment METAL_DETECTOR()
 * @method static PoseidonEnchantment POSEIDON()
 * @method static SuperGlueEnchantment SUPER_GLUE()
 * @method static SonarEnchantment SONAR()
 * @method static ThermalHookEnchantment THERMAL_HOOK()
 * 
 * Armor
 * @method static AbsorbEnchantment ABSORB()
 * @method static AntiKnockbackEnchantment ANTI_KNOCKBACK()
 * @method static BlessingEnchantment BLESSING()
 * @method static BunnyEnchantment BUNNY()
 * @method static CrouchEnchantment CROUCH()
 * @method static DodgeEnchantment DODGE()
 * @method static EnduranceEnchantment ENDURANCE()
 * @method static FireballEnchantment FIREBALL()
 * @method static ForesightEnchantment FORESIGHT()
 * @method static GearsEnchantment GEARS()
 * @method static GlowingEnchantment GLOWING()
 * @method static GodlyRetributionEnchantment GODLY_RETRIBUTION()
 * @method static ObsidianEnchantment OBSIDIAN()
 * @method static OverlordEnchantment OVERLORD()
 * @method static RageEnchantment RAGE()
 * @method static SnareEnchantment SNARE()
 * @method static SorceryEnchantment SORCERY()
 * 
 * Axe
 * @method static DebarkerEnchantment DEBARKER()
 * @method static LumberjackEnchantment LUMBERJACK()
 * @method static WormEnchantment WORM()
 * 
 * Hoe
 * @method static BurrowEnchantment BURROW()
 * @method static CapsuleEnchantment CAPSULE()
 * @method static FertilizeEnchantment FERTILIZE()
 * @method static HarvestEnchantment HARVEST()
 * @method static TillerEnchantment TILLER()
 * @method static VendorEnchantment VENDOR()
 */
class EnchantmentRegistry {

	private static array $_registry = [];
	/** @var Enchantment[] $enchantments */
	private static array $enchantments = [];

	private static function _registryRegister(string $id, Enchantment $ench) {
		self::$_registry[$id] = $ench;
	}

	private static function _registryGet(string $id): ?Enchantment {
		if (!isset(self::$_registry[$id])) return null;

		return self::$_registry[$id];
	}

	public static function __callStatic($name, $arguments) {
		return self::_registryGet($name);
	}

	public static function setup(): void {
		foreach (ED::ENCHANTMENTS as $id => $data) {
			$class = $data[ED::DATA_CLASS] ?? Enchantment::class;

			self::$enchantments[$id] = new $class($id, $data[ED::DATA_EXTRAS] ?? []);
			self::_registryRegister(str_replace(' ', '_', strtoupper(self::$enchantments[$id]->getName())), self::$enchantments[$id]);
		}
	}

	public static function getEWE(EnchantmentInstance $enchantment): ?Enchantment {
		return self::getEnchantment(EnchantmentIdMap::getInstance()->toId($enchantment->getType()))?->setStoredLevel($enchantment->getLevel());
	}

	public static function getEnchantment(int $id): ?Enchantment {
		return self::$enchantments[$id] ?? null;
	}

	public static function getEnchantmentByName(string $name): ?Enchantment {
		foreach (self::$enchantments as $ench) {
			if (strtolower($ench->getName()) == strtolower($name)) return $ench;
		}
		return null;
	}

	/** @return Enchantment[] */
	public static function getEnchantments(int $rarity = -1, int $category = ED::CAT_UNIVERSAL, bool $showDisabled = false): array {
		$enchantments = [];

		if ($rarity == -1) {
			if ($showDisabled) $enchantments = self::$enchantments;
			else {
				foreach (self::$enchantments as $enchantment) {
					if (!$enchantment->isDisabled()) $enchantments[$enchantment->getRuntimeId()] = $enchantment;
				}
			}
		} else {
			foreach (self::$enchantments as $enchantment) {
				if ((!$enchantment->isDisabled() || $showDisabled) && $enchantment->getRarity() === $rarity) {
					$enchantments[$enchantment->getRuntimeId()] = $enchantment;
				}
			}
		}

		foreach ($enchantments as $key => $enchantment) {
			if ($category !== ED::CAT_UNIVERSAL && ED::slotsToCategory($enchantment->getType()) !== $category && !$enchantment->getType()->get(ED::SLOT_ALL)) {
				unset($enchantments[$key]);
			}
		}
		return $enchantments;
	}

	public static function getRandomEnchantment(int $rarity = ED::RARITY_COMMON, int $category = ED::CAT_UNIVERSAL): Enchantment {
		$enchantments = self::getEnchantments($rarity, $category);
		$iterations = 0;
		$originalCategory = $category;
		while (empty($enchantments)) {
			$iterations++;
			$enchantments = self::getEnchantments($rarity, $category = match ($category) {
				ED::CAT_PICKAXE => ED::CAT_TOOL,
				ED::CAT_FISHING_ROD => ED::CAT_TOOL,
				ED::CAT_SWORD => ED::CAT_ARMOR,
				ED::CAT_ARMOR => ED::CAT_SWORD,
				default => ED::CAT_UNIVERSAL,
			});
			if ($iterations > 5) {
				throw new \RuntimeException("Failed to find enchantments for rarity $rarity and category $originalCategory after 5 iterations.");
			}
		}
		return $enchantments[array_rand($enchantments)];
	}
}

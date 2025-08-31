<?php

declare(strict_types=1);

namespace skyblock\enchantments;

use core\items\Elytra;
use core\items\type\TieredTool;
use core\utils\conversion\LegacyItemIds;
use core\utils\Utils;
use pocketmine\block\BlockToolType;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\{
	Item,
	Bow,
	Axe,
	Pickaxe,
	Sword,
	Shears,
	Shovel,
	Hoe,
	enchantment\ItemFlags,
	FishingRod as PMFishingRod
};
use pocketmine\network\mcpe\protocol\serializer\BitSet;
use pocketmine\utils\TextFormat;
use skyblock\enchantments\type\armor\AbsorbEnchantment;
use skyblock\enchantments\type\armor\AntiKnockbackEnchantment;
use skyblock\enchantments\type\armor\BlessingEnchantment;
use skyblock\enchantments\type\armor\boots\BunnyEnchantment;
use skyblock\enchantments\type\armor\boots\GearsEnchantment;
use skyblock\enchantments\type\armor\chestplate\RageEnchantment;
use skyblock\enchantments\type\armor\leggings\CrouchEnchantment;
use skyblock\enchantments\type\armor\DodgeEnchantment;
use skyblock\enchantments\type\armor\EnduranceEnchantment;
use skyblock\enchantments\type\armor\FireballEnchantment;
use skyblock\enchantments\type\armor\GodlyRetributionEnchantment;
use skyblock\enchantments\type\armor\helmet\ForesightEnchantment;
use skyblock\enchantments\type\armor\helmet\GlowingEnchantment;
use skyblock\enchantments\type\armor\ObsidianEnchantment;
use skyblock\enchantments\type\armor\OverlordEnchantment;
use skyblock\enchantments\type\armor\ProtectionEnchantment;
use skyblock\enchantments\type\armor\SnareEnchantment;
use skyblock\enchantments\type\armor\SorceryEnchantment;
use skyblock\enchantments\type\Enchantment;
use skyblock\enchantments\type\tool\axe\DebarkerEnchantment;
use skyblock\enchantments\type\tool\axe\LumberjackEnchantment;
use skyblock\enchantments\type\tool\hoe\BurrowEnchantment;
use skyblock\enchantments\type\tool\axe\WormEnchantment;
use skyblock\enchantments\type\tool\hoe\CapsuleEnchantment;
use skyblock\enchantments\type\tool\hoe\FertilizeEnchantment;
use skyblock\enchantments\type\tool\hoe\HarvestEnchantment;
use skyblock\enchantments\type\tool\hoe\TillerEnchantment;
use skyblock\enchantments\type\tool\hoe\VendorEnchantment;
use skyblock\enchantments\type\tool\rod\FlingEnchantment;
use skyblock\enchantments\type\tool\rod\MetalDetectorEnchantment;
use skyblock\enchantments\type\tool\rod\PoseidonEnchantment;
use skyblock\enchantments\type\tool\rod\SonarEnchantment;
use skyblock\enchantments\type\tool\rod\SuperGlueEnchantment;
use skyblock\enchantments\type\tool\pickaxe\AutoSmeltEnchantment;
use skyblock\enchantments\type\tool\pickaxe\FeatherWeightEnchantment;
use skyblock\enchantments\type\tool\pickaxe\FeedEnchantment;
use skyblock\enchantments\type\tool\pickaxe\KeyMagnetEnchantment;
use skyblock\enchantments\type\tool\pickaxe\KeyRushEnchantment;
use skyblock\enchantments\type\tool\pickaxe\MagnifyEnchantment;
use skyblock\enchantments\type\tool\pickaxe\PurifyEnchantment;
use skyblock\enchantments\type\tool\pickaxe\SiftEnchantment;
use skyblock\enchantments\type\tool\pickaxe\TransfusionEnchantment;
use skyblock\enchantments\type\tool\rod\ThermalHookEnchantment;
use skyblock\enchantments\type\tool\sword\BackstabEnchantment;
use skyblock\enchantments\type\tool\sword\BleedEnchantment;
use skyblock\enchantments\type\tool\sword\DazeEnchantment;
use skyblock\enchantments\type\tool\sword\DecayEnchantment;
use skyblock\enchantments\type\tool\sword\ElectrifyEnchantment;
use skyblock\enchantments\type\tool\sword\ExecuteEnchantment;
use skyblock\enchantments\type\tool\sword\GrindEnchantment;
use skyblock\enchantments\type\tool\sword\HadesEnchantment;
use skyblock\enchantments\type\tool\sword\KeyTheftEnchantment;
use skyblock\enchantments\type\tool\sword\LifestealEnchantment;
use skyblock\enchantments\type\tool\sword\OofEnchantment;
use skyblock\enchantments\type\tool\sword\PierceEnchantment;
use skyblock\enchantments\type\tool\sword\RadiationEnchantment;
use skyblock\enchantments\type\tool\sword\ShuffleEnchantment;
use skyblock\enchantments\type\tool\sword\SpiteEnchantment;
use skyblock\enchantments\type\tool\sword\StarvationEnchantment;
use skyblock\enchantments\type\tool\sword\TechBlastEnchantment;
use skyblock\enchantments\type\tool\sword\TidesEnchantment;
use skyblock\enchantments\type\tool\sword\UpliftEnchantment;
use skyblock\enchantments\type\tool\sword\ZeusEnchantment;
use skyblock\enchantments\type\universal\UnbreakingEnchantment;
use skyblock\fishing\item\FishingRod;

class EnchantmentData{

	public const DATA_NAME = "name";
	public const DATA_DESCRIPTION = "description";
	public const DATA_MAX_LEVEL = "maxLevel";
	public const DATA_RARITY = "rarity";
	public const DATA_TYPE = "type";
	public const DATA_STACKABLE = "stackable";
	public const DATA_STACK_LEVEL = "stackLevel";
	public const DATA_MAX_STACK = "maxStack";
	public const DATA_OVERCLOCK = "overclock";
	public const DATA_OBTAINABLE = "obtainable";
	public const DATA_HANDLED = "handled";
	public const DATA_DISABLED = "disabled";
	public const DATA_ITEM_FLAG = "slot_id";
	public const DATA_CLASS = "class";
	public const DATA_EXTRAS = "extras";

	public const RARITY_COMMON = 1;
	public const RARITY_UNCOMMON = 2;
	public const RARITY_RARE = 3;
	public const RARITY_LEGENDARY = 4;
	public const RARITY_DIVINE = 5;

	public const CAT_UNIVERSAL = 0;
	public const CAT_PICKAXE = 1;
	public const CAT_TOOL = 2;
	public const CAT_SWORD = 3;
	public const CAT_ARMOR = 4;
	public const CAT_FISHING_ROD = 5;

	// universal
	public const SLOT_NONE = 0;
	public const SLOT_ALL = 1;
	// armor
	public const SLOT_ARMOR = 2;
	public const SLOT_HEAD = 3;
	public const SLOT_TORSO = 4;
	public const SLOT_LEGS = 5;
	public const SLOT_FEET = 6;
	// weapon
	public const SLOT_SWORD = 7;
	public const SLOT_BOW = 8;
	// tool
	public const SLOT_TOOL = 9;
	public const SLOT_HOE = 10;
	public const SLOT_SHEARS = 11;
	public const SLOT_FLINT_AND_STEEL = 12;
	public const SLOT_DIG = 13;
	public const SLOT_AXE = 14;
	public const SLOT_PICKAXE = 15;
	public const SLOT_SHOVEL = 16;
	public const SLOT_FISHING_ROD = 17;
	public const SLOT_CARROT_STICK = 18;
	public const SLOT_ELYTRA = 19;
	public const SLOT_TRIDENT = 20;
	// melee
	public const SLOT_MELEE = 21;



	const ENCHANTMENTS = [
		#region Vanilla Enchantments
		self::BLAST_PROTECTION => [
			self::DATA_NAME => "Blast Protection",
			self::DATA_DESCRIPTION => "Take less damage",
			self::DATA_MAX_LEVEL => 4,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_CLASS => ProtectionEnchantment::class,
			self::DATA_EXTRAS => [
				"type_modifier" => 1.5,
				"applicable_damage_types" => [
					EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
					EntityDamageEvent::CAUSE_ENTITY_EXPLOSION,
				]
			],
		],
		self::EFFICIENCY => [
			self::DATA_NAME => "Efficiency",
			self::DATA_DESCRIPTION => "Makes mining blocks way faster",
			self::DATA_MAX_LEVEL => 5,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_DIG,
		],
		// self::FIRE_ASPECT => [
		// 	self::DATA_NAME => "Fire Aspect",
		// 	self::DATA_DESCRIPTION => "Light enemies on fire",
		// 	self::DATA_MAX_LEVEL => 1,
		// 	self::DATA_RARITY => self::RARITY_LEGENDARY,
		// 	self::DATA_TYPE => self::SLOT_SWORD,
		// 	self::DATA_HANDLED => false,
		// 	self::DATA_OVERCLOCK => false
		// ],
		self::FIRE_PROTECTION => [
			self::DATA_NAME => "Fire Protection",
			self::DATA_DESCRIPTION => "Take less fire damage",
			self::DATA_MAX_LEVEL => 4,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_CLASS => ProtectionEnchantment::class,
			self::DATA_EXTRAS => [
				"type_modifier" => 1.25,
				"applicable_damage_types" => [
					EntityDamageEvent::CAUSE_FIRE,
					EntityDamageEvent::CAUSE_FIRE_TICK
				]
			]
		],
		//self::KNOCKBACK => [
		//	self::DATA_NAME => "Knockback",
		//	self::DATA_DESCRIPTION => "Increased Knockback to opponents",
		//	self::DATA_MAX_LEVEL => 2,
		//	self::DATA_RARITY => self::RARITY_LEGENDARY,
		//	self::DATA_TYPE => self::SLOT_SWORD,
		//	self::DATA_HANDLED => false,
		//	self::DATA_OVERCLOCK => false,
		//],
		self::MENDING => [
			self::DATA_NAME => "Mending",
			self::DATA_DESCRIPTION => "Repairs your pickaxe with mining EXP",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_HANDLED => false,
		],
		self::PROJECTILE_PROTECTION => [
			self::DATA_NAME => "Projectile Protection",
			self::DATA_DESCRIPTION => "Take less projectile damage",
			self::DATA_MAX_LEVEL => 4,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_CLASS => ProtectionEnchantment::class,
			self::DATA_EXTRAS => [
				"type_modifier" => 1.5,
				"applicable_damage_types" => [
					EntityDamageEvent::CAUSE_PROJECTILE
				]
			]
		],
		self::PROTECTION => [
			self::DATA_NAME => "Protection",
			self::DATA_DESCRIPTION => "Take less normal damage",
			self::DATA_MAX_LEVEL => 4,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_STACKABLE => true,
			self::DATA_MAX_STACK => 12,
			self::DATA_CLASS => ProtectionEnchantment::class,
			self::DATA_EXTRAS => [
				"type_modifier" => 0.75
			]
		],
		self::SILK_TOUCH => [
			self::DATA_NAME => "Silk Touch",
			self::DATA_DESCRIPTION => "Gives you blocks in their mined state",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_DIG,
			self::DATA_HANDLED => false,
		],
		self::UNBREAKING => [
			self::DATA_NAME => "Unbreaking",
			self::DATA_DESCRIPTION => "Makes your utilities take longer to break",
			self::DATA_MAX_LEVEL => 5,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_ALL,
			self::DATA_CLASS => UnbreakingEnchantment::class
		],
		#endregion

		self::TRANSMUTATION => [
			self::DATA_NAME => "Transmutation",
			self::DATA_DESCRIPTION => "Increases the amount of essence found",
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => [self::SLOT_TOOL, self::SLOT_DIG],
			self::DATA_MAX_LEVEL => 5,
		],

		#region Armor Enchantemnts
		self::BUNNY => [
			self::DATA_NAME => "Bunny",
			self::DATA_DESCRIPTION => "Gives you permanent Jump Boost while using",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_FEET,
			self::DATA_CLASS => BunnyEnchantment::class,
		],
		self::CROUCH => [
			self::DATA_NAME => "Crouch",
			self::DATA_DESCRIPTION => "Chance to highly decrease damage while crouching",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_TYPE => self::SLOT_LEGS,
			self::DATA_CLASS => CrouchEnchantment::class,
		],
		self::FORESIGHT => [
			self::DATA_NAME => "Foresight",
			self::DATA_DESCRIPTION => "Chance when getting hit to decrease damage taken from the next three hits received",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_HEAD,
			self::DATA_CLASS => ForesightEnchantment::class,
		],
		self::GEARS => [
			self::DATA_NAME => "Gears",
			self::DATA_DESCRIPTION => "Gives you permanent Speed while using",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_FEET,
			self::DATA_CLASS => GearsEnchantment::class,
		],
		self::GLOWING => [
			self::DATA_NAME => "Glowing",
			self::DATA_DESCRIPTION => "Gives you permanent Night Vision while using",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_TYPE => self::SLOT_HEAD,
			self::DATA_CLASS => GlowingEnchantment::class
		],
		self::GODLY_RETRIBUTION => [
			self::DATA_NAME => "Godly Retribution",
			self::DATA_DESCRIPTION => "Strength and regeneration for a short period when close to dying",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_DISABLED => true,
			self::DATA_CLASS => GodlyRetributionEnchantment::class,
		],
		self::SNARE => [
			self::DATA_NAME => "Snare",
			self::DATA_DESCRIPTION => "Hooks your enemies and drags them towards you",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_CLASS => SnareEnchantment::class,
		],
		self::THORNS => [
			self::DATA_NAME => "Thorns",
			self::DATA_DESCRIPTION => "Chance to automatically damage your attackers",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_DISABLED => true,
		],
		self::SORCERY => [
			self::DATA_NAME => "Sorcery",
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_MAX_LEVEL => 2,
			self::DATA_DESCRIPTION => "Gives you a random positive effect",
			self::DATA_CLASS => SorceryEnchantment::class
		],
		self::BLESSING => [
			self::DATA_NAME => "Blessing",
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_MAX_LEVEL => 3,
			self::DATA_DESCRIPTION => "Chance to remove your negative effects and give them to the enemy",
			self::DATA_CLASS => BlessingEnchantment::class
		],
		self::FIREBALL => [
			self::DATA_NAME => "Fireball",
			self::DATA_DESCRIPTION => "Chance to set nearby enemies ablaze",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_STACKABLE => true,
			self::DATA_MAX_STACK => 4,
			self::DATA_CLASS => FireballEnchantment::class,
		],
		self::OBSIDIAN => [
			self::DATA_NAME => "Obsidian",
			self::DATA_DESCRIPTION => "Attacks do less damage",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_STACKABLE => true,
			self::DATA_MAX_STACK => 2,
			self::DATA_CLASS => ObsidianEnchantment::class,
		],
		self::ANTI_KNOCKBACK => [
			self::DATA_NAME => "Anti Knockback",
			self::DATA_DESCRIPTION => "Chance to cancel knockback from attacks",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_STACKABLE => true,
			self::DATA_MAX_STACK => 3,
			self::DATA_DISABLED => true,
			self::DATA_CLASS => AntiKnockbackEnchantment::class,
		],
		self::OVERLORD => [
			self::DATA_NAME => "Overlord",
			self::DATA_DESCRIPTION => "Increases maximum health",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_STACKABLE => true,
			self::DATA_MAX_STACK => 5,
			self::DATA_CLASS => OverlordEnchantment::class,
		],
		self::DODGE => [
			self::DATA_NAME => "Dodge",
			self::DATA_DESCRIPTION => "Has a chance to completely block some attacks, but decreases armor durability quicker",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_STACKABLE => true,
			self::DATA_MAX_STACK => 2,
			self::DATA_CLASS => DodgeEnchantment::class,
		],
		self::RAGE => [
			self::DATA_NAME => "Rage",
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_TORSO,
			self::DATA_MAX_LEVEL => 3,
			self::DATA_DESCRIPTION => "Chance to give you strength and resistance",
			self::DATA_CLASS => RageEnchantment::class,
		],
		self::ENDURANCE => [
			self::DATA_NAME => "Endurance",
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_MAX_LEVEL => 2,
			self::DATA_DESCRIPTION => "Chance to take durability from your opponent, and apply it to your own armor. Must attack back after a while for enchantment to work.",
			self::DATA_CLASS => EnduranceEnchantment::class,
		],
		self::ABSORB => [
			self::DATA_NAME => "Absorb",
			self::DATA_DESCRIPTION => "Stores up damage for a few seconds and heals from a percent of the damage",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_ARMOR,
			self::DATA_CLASS => AbsorbEnchantment::class,
		],
		#endregion

		#region Pickaxe Enchantments
		self::AUTOSMELT => [
			self::DATA_NAME => "Autosmelt",
			self::DATA_DESCRIPTION => "Automatically smelts ores mined",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_CLASS => AutoSmeltEnchantment::class,
		],
		self::FEED => [
			self::DATA_NAME => "Feed",
			self::DATA_DESCRIPTION => "Chance to fill your hunger bar while mining",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_CLASS => FeedEnchantment::class,
		],
		self::FEATHER_WEIGHT => [
			self::DATA_NAME => "Feather Weight",
			self::DATA_DESCRIPTION => "Chance to give you haste 2 while mining",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => FeatherWeightEnchantment::class,
		],
		self::KEY_MAGNET => [
			self::DATA_NAME => "Key Magnet",
			self::DATA_DESCRIPTION => "Multiplied key finding chances while mining",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_CLASS => KeyMagnetEnchantment::class,
		],
		self::KEY_RUSH => [
			self::DATA_NAME => "Key Rush",
			self::DATA_DESCRIPTION => "Chance to give more keys whenever you stumble upon them",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_HANDLED => true,
			self::DATA_CLASS => KeyRushEnchantment::class,
		],
		self::MAGNIFY => [
			self::DATA_NAME => "Magnify",
			self::DATA_DESCRIPTION => "Chance to increase XP drops from breaking blocks",
			self::DATA_MAX_LEVEL => 4,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_DISABLED => true,
			self::DATA_CLASS => MagnifyEnchantment::class,
		],
		self::PURIFY => [
			self::DATA_NAME => "Purify",
			self::DATA_DESCRIPTION => "Chance to automatically sell mined ores for a multiplied value",
			self::DATA_MAX_LEVEL => 4,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_CLASS => PurifyEnchantment::class,
		],
		self::SIFT => [
			self::DATA_NAME => "Sift",
			self::DATA_DESCRIPTION => "Chance of getting more of an ore drop while mining",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => SiftEnchantment::class,
		],
		self::TRANSFUSION => [
			self::DATA_NAME => "Transfusion",
			self::DATA_DESCRIPTION => "Chance of changing mined ore to next tier",
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_PICKAXE,
			self::DATA_MAX_LEVEL => 3,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => TransfusionEnchantment::class,
		],
		#endregion

		#region Axe Enchantments
		self::DEBARKER => [
			self::DATA_NAME => "Debarker",
			self::DATA_DESCRIPTION => "Sneak & Click/Tap the bottom of a tree to strip the whole trunk",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_AXE,
			self::DATA_CLASS => DebarkerEnchantment::class
		],
		self::LUMBERJACK => [
			self::DATA_NAME => "Lumberjack",
			self::DATA_DESCRIPTION => "Sneak and mine the bottom of a tree to break the whole trunk",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_AXE,
			self::DATA_CLASS => LumberjackEnchantment::class
		],
		self::WORM => [
			self::DATA_NAME => "Worm",
			self::DATA_DESCRIPTION => "Break Crops in a horizontal line",
			self::DATA_MAX_LEVEL => 5,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_AXE,
			self::DATA_CLASS => WormEnchantment::class
		],
		#endregion

		#region Sword Enchantments
		self::BACKSTAB => [
			self::DATA_NAME => "Backstab",
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_MAX_LEVEL => 2,
			self::DATA_DESCRIPTION => "More damage is done if the player you're attacking isn't facing you",
			self::DATA_CLASS => BackstabEnchantment::class,
		],
		self::BLEED => [
			self::DATA_NAME => "Bleed",
			self::DATA_DESCRIPTION => "Causes opponent to slowly bleed out when hitting them",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_CLASS => BleedEnchantment::class,
		],
		// self::COMBO => [
		// 	self::DATA_NAME => "Combo",
		// 	self::DATA_DESCRIPTION => "Raises your weapon damage the higher your combo is",
		// 	self::DATA_MAX_LEVEL => 1,
		// 	self::DATA_RARITY => self::RARITY_DIVINE,
		// 	self::DATA_TYPE => self::SLOT_SWORD,
		// 	self::DATA_OVERCLOCK => false,
		// 	self::DATA_HANDLED => false
		// ],
		self::DAZE => [
			self::DATA_NAME => "Daze",
			self::DATA_DESCRIPTION => "Gives your opponent nausea",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => DazeEnchantment::class,
		],
		self::DECAY => [
			self::DATA_NAME => "Decay",
			self::DATA_DESCRIPTION => "Chance of inflicting wither effect on enemies",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_DISABLED => true,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => DecayEnchantment::class,
		],
		self::ELECTRIFY => [
			self::DATA_NAME => "Electrify",
			self::DATA_DESCRIPTION => "Strikes your opponent with lightning and slows them down",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_CLASS => ElectrifyEnchantment::class,
		],
		self::EXECUTE => [
			self::DATA_NAME => "Execute",
			self::DATA_DESCRIPTION => "More damage done when enemies have lower health",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_CLASS => ExecuteEnchantment::class,
		],
		self::GRIND => [
			self::DATA_NAME => "Grind",
			self::DATA_DESCRIPTION => "Causes more XP to drop when killing Mob Spawner mobs",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_DISABLED => true,
			self::DATA_CLASS => GrindEnchantment::class,
		],
		self::HADES => [
			self::DATA_NAME => "Hades",
			self::DATA_DESCRIPTION => "Chance of extra damage + wither + fire damage + awsum particles",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_OVERCLOCK => true,
			self::DATA_HANDLED => true,
			self::DATA_CLASS => HadesEnchantment::class,
			//self::DATA_DISABLED => true
		],
		// self::KABOOM => [
		// 	self::DATA_NAME => "Kaboom",
		// 	self::DATA_DESCRIPTION => "Chance of explosive damage",
		// 	self::DATA_MAX_LEVEL => 3,
		// 	self::DATA_RARITY => self::RARITY_LEGENDARY,
		// 	self::DATA_TYPE => self::SLOT_SWORD,
		// 	self::DATA_HANDLED => true
		//  self::DATA_CLASS => KaboomEnchantment::class,
		// ],
		self::KEY_THEFT => [
			self::DATA_NAME => "Key Theft",
			self::DATA_DESCRIPTION => "Chance of stealing keys from a player when they die. (1 to 3 keys max depending on level)",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_HANDLED => true,
			self::DATA_CLASS => KeyTheftEnchantment::class,
		],
		self::LIFESTEAL => [
			self::DATA_NAME => "Lifesteal",
			self::DATA_DESCRIPTION => "Gives you health that is taken from damaging opponents",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => LifestealEnchantment::class,
		],
		self::OOF => [
			self::DATA_NAME => "OOF",
			self::DATA_DESCRIPTION => "OOF Sounds when doing damage",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_HANDLED => true,
			self::DATA_CLASS => OofEnchantment::class,
		],
		self::PIERCE => [
			self::DATA_NAME => "Pierce",
			self::DATA_DESCRIPTION => "Multiplies damage to armor, and increase overall damage",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_CLASS => PierceEnchantment::class,
		],
		self::RADIATION => [
			self::DATA_NAME => "Radiation",
			self::DATA_DESCRIPTION => "Causes heavy slowness and poison",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => RadiationEnchantment::class,
		],
		self::SHUFFLE => [
			self::DATA_NAME => "Shuffle",
			self::DATA_DESCRIPTION => "Chance of changing enemy's held item slot or shuffling their entire hotbar",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_CLASS => ShuffleEnchantment::class,
		],
		self::SPITE => [
			self::DATA_NAME => "Spite",
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_MAX_LEVEL => 3,
			self::DATA_DESCRIPTION => "Chance to take your missing health away from your opponent",
			self::DATA_HANDLED => true,
			self::DATA_DISABLED => false,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => SpiteEnchantment::class,
		],
		self::STARVATION => [
			self::DATA_NAME => "Starvation",
			self::DATA_DESCRIPTION => "Make your enemies go hungry faster than normal",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => StarvationEnchantment::class,
		],
		self::TECH_BLAST => [
			self::DATA_NAME => "Tech Blast",
			self::DATA_DESCRIPTION => "Causes all players within 4 blocks to be blasted away from you when activated",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_CLASS => TechBlastEnchantment::class,
		],
		self::TIDES => [
			self::DATA_NAME => "Tides",
			self::DATA_DESCRIPTION => "Chance of extra knockback and splash damage",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_HANDLED => true,
			self::DATA_CLASS => TidesEnchantment::class,
		],
		self::UPLIFT => [
			self::DATA_NAME => "Uplift",
			self::DATA_DESCRIPTION => "Launch enemies up high!",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_HANDLED => true,
			self::DATA_CLASS => UpliftEnchantment::class,
		],
		self::ZEUS => [
			self::DATA_NAME => "Zeus",
			self::DATA_DESCRIPTION => "Chance to double damage and strike lightning on enemy",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_SWORD,
			self::DATA_HANDLED => true,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => ZeusEnchantment::class,
		],
		#endregion

		#region Fishing Enchantments
		// Unhandled, but needs handled to add the enchantments. All handled in FishingHook class
		self::FLING => [
			self::DATA_NAME => "Fling",
			self::DATA_DESCRIPTION => "Fling yourself towards the fishing hook",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_FISHING_ROD,
			self::DATA_DISABLED => true,
			self::DATA_CLASS => FlingEnchantment::class,
		],
		self::METAL_DETECTOR => [
			self::DATA_NAME => "Metal Detector",
			self::DATA_DESCRIPTION => "Find more keys while fishing",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_FISHING_ROD,
			self::DATA_CLASS => MetalDetectorEnchantment::class,
		],
		self::POSEIDON => [
			self::DATA_NAME => "Poseidon",
			self::DATA_DESCRIPTION => "Attracts fish to your hook faster",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_TYPE => self::SLOT_FISHING_ROD,
			self::DATA_CLASS => PoseidonEnchantment::class,
		],
		self::SONAR => [
			self::DATA_NAME => "Sonar",
			self::DATA_DESCRIPTION => "Increases the chance to fish up rare items",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_FISHING_ROD,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => SonarEnchantment::class,
		],
		self::SUPER_GLUE => [
			self::DATA_NAME => "Super Glue",
			self::DATA_DESCRIPTION => "Tugging on your fishing hook will last longer",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_TYPE => self::SLOT_FISHING_ROD,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => SuperGlueEnchantment::class,
		],
		self::THERMAL_HOOK => [
			self::DATA_NAME => "Thermal Hook",
			self::DATA_DESCRIPTION => "Allows for fishing in lava pools",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_FISHING_ROD,
			self::DATA_CLASS => ThermalHookEnchantment::class,
		],
		#endregion

		#region Hoe Enchantments
		self::BURROW => [
			self::DATA_NAME => "Burrow",
			self::DATA_DESCRIPTION => "Chance to find keys while farming",
			self::DATA_MAX_LEVEL => 5,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_HOE,
			self::DATA_CLASS => BurrowEnchantment::class
		],
		self::CAPSULE => [
			self::DATA_NAME => "Capsule",
			self::DATA_DESCRIPTION => "Chance to find special items while farming",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_HOE,
			self::DATA_OVERCLOCK => true,
			self::DATA_CLASS => CapsuleEnchantment::class
		],
		self::FERTILIZE => [
			self::DATA_NAME => "Fertilize",
			self::DATA_DESCRIPTION => "Can replant crops if the seed is in your inventory, but has an additional chance for a replanted crop to start in a later growth stage",
			self::DATA_MAX_LEVEL => 3,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_TYPE => self::SLOT_HOE,
			self::DATA_CLASS => FertilizeEnchantment::class,
		],
		self::HARVEST => [
			self::DATA_NAME => "Harvest",
			self::DATA_DESCRIPTION => "Break other crops around the one you break.",
			self::DATA_MAX_LEVEL => 2,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_TYPE => self::SLOT_HOE,
			self::DATA_CLASS => HarvestEnchantment::class,
		],
		self::TILLER => [
			self::DATA_NAME => "Tiller",
			self::DATA_DESCRIPTION => "Tills the ground around the block.",
			self::DATA_MAX_LEVEL => 4,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_TYPE => self::SLOT_HOE,
			self::DATA_CLASS => TillerEnchantment::class
		],
		self::VENDOR => [
			self::DATA_NAME => "Vendor",
			self::DATA_DESCRIPTION => "Auto sells your crops",
			self::DATA_MAX_LEVEL => 1,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_TYPE => self::SLOT_HOE,
			self::DATA_CLASS => VendorEnchantment::class
		],

		//Helmet

		//Chestplate

		//Leggings

		//Boots
	];

	const SLOTS = [
		self::SLOT_NONE => [
			self::DATA_NAME => "None",
			self::DATA_ITEM_FLAG => ItemFlags::NONE,
		],
		self::SLOT_ALL => [
			self::DATA_NAME => "Universal",
			self::DATA_ITEM_FLAG => ItemFlags::ALL,
		],

		self::SLOT_ARMOR => [
			self::DATA_NAME => "Armor",
			self::DATA_ITEM_FLAG => ItemFlags::ARMOR,
		],
		self::SLOT_HEAD => [
			self::DATA_NAME => "Helmet",
			self::DATA_ITEM_FLAG => ItemFlags::HEAD,
		],
		self::SLOT_TORSO => [
			self::DATA_NAME => "Chestplate",
			self::DATA_ITEM_FLAG => ItemFlags::TORSO,
		],
		self::SLOT_LEGS => [
			self::DATA_NAME => "Leggings",
			self::DATA_ITEM_FLAG => ItemFlags::LEGS,
		],
		self::SLOT_FEET => [
			self::DATA_NAME => "Boots",
			self::DATA_ITEM_FLAG => ItemFlags::FEET,
		],

		self::SLOT_SWORD => [
			self::DATA_NAME => "Sword",
			self::DATA_ITEM_FLAG => ItemFlags::SWORD,
		],
		self::SLOT_BOW => [
			self::DATA_NAME => "Bow",
			self::DATA_ITEM_FLAG => ItemFlags::BOW,
		],

		self::SLOT_TOOL => [
			self::DATA_NAME => "Tool",
			self::DATA_ITEM_FLAG => ItemFlags::TOOL,
		],
		self::SLOT_HOE => [
			self::DATA_NAME => "Hoe",
			self::DATA_ITEM_FLAG => ItemFlags::HOE,
		],
		self::SLOT_SHEARS => [
			self::DATA_NAME => "Shears",
			self::DATA_ITEM_FLAG => ItemFlags::SHEARS,
		],
		self::SLOT_FLINT_AND_STEEL => [
			self::DATA_NAME => "Flint and Steel",
			self::DATA_ITEM_FLAG => ItemFlags::FLINT_AND_STEEL,
		],

		self::SLOT_DIG => [
			self::DATA_NAME => "Dig",
			self::DATA_ITEM_FLAG => ItemFlags::DIG,
		],
		self::SLOT_AXE => [
			self::DATA_NAME => "Axe",
			self::DATA_ITEM_FLAG => ItemFlags::AXE,
		],
		self::SLOT_PICKAXE => [
			self::DATA_NAME => "Pickaxe",
			self::DATA_ITEM_FLAG => ItemFlags::PICKAXE,
		],
		self::SLOT_SHOVEL => [
			self::DATA_NAME => "Shovel",
			self::DATA_ITEM_FLAG => ItemFlags::SHOVEL,
		],

		self::SLOT_FISHING_ROD => [
			self::DATA_NAME => "Fishing Rod",
			self::DATA_ITEM_FLAG => ItemFlags::FISHING_ROD,
		],
		self::SLOT_CARROT_STICK => [
			self::DATA_NAME => "Carrot on a Stick",
			self::DATA_ITEM_FLAG => ItemFlags::CARROT_STICK,
		],
		self::SLOT_ELYTRA => [
			self::DATA_NAME => "Elytra",
			self::DATA_ITEM_FLAG => ItemFlags::ELYTRA,
		],
		self::SLOT_TRIDENT => [
			self::DATA_NAME => "Trident",
			self::DATA_ITEM_FLAG => ItemFlags::TRIDENT,
		],

		self::SLOT_MELEE => [
			self::DATA_NAME => "Melee",
			self::DATA_ITEM_FLAG => (ItemFlags::SWORD | ItemFlags::AXE),
		],
	];

	const ITEM_FLAG_NAMES = [
		ItemFlags::NONE => "None",
		ItemFlags::ALL => "Universal",

		ItemFlags::ARMOR => "Armor",
		ItemFlags::HEAD => "Helmet",
		ItemFlags::TORSO => "Chestplate",
		ItemFlags::LEGS => "Leggings",
		ItemFlags::FEET => "Boots",

		ItemFlags::SWORD => "Sword",
		ItemFlags::BOW => "Bow",

		ItemFlags::TOOL => "Tool",
		ItemFlags::HOE => "Hoe",
		ItemFlags::SHEARS => "Shears",
		ItemFlags::FLINT_AND_STEEL => "Flint and Steel",

		ItemFlags::DIG => "Dig",
		ItemFlags::AXE => "Axe",
		ItemFlags::PICKAXE => "Pickaxe",
		ItemFlags::SHOVEL => "Shovel",

		ItemFlags::FISHING_ROD => "Fishing Rod",

		(ItemFlags::SWORD | ItemFlags::AXE) => "Melee",
	];

	const DAMAGE_ENCHANTMENTS = [
		// self::KABOOM,
		self::ZEUS,
		self::HADES,
		self::SPITE,

	];

	const CONVERT = [ //when enchantments go shit
		270 => self::LUMBERJACK,
		271 => self::LUMBERJACK,
	];

	/**
	 * @return int[]
	 */
	public static function typeToEtype(BitSet $type): array {
		$types = [];
		foreach (self::SLOTS as $etype => $data) {
			/** @var int $etype */
			if ($type->get($etype)) $types[] = $data[self::DATA_ITEM_FLAG];
		}
		return empty($types) ? 0x0 : $types;
	}

	public static function etypeToType(int $etype): int {
		/** @var int $id */
		foreach (self::SLOTS as $id => $data) {
			if ($data[self::DATA_ITEM_FLAG] == $etype) return $id;
		}
		return -1;
	}

	public static function getItemType(Item $item): int {
		switch (true) {
			case in_array(LegacyItemIds::typeIdToLegacyId($item->getTypeId()), [298, 302, 306, 310, 314, 748]):
				return self::SLOT_HEAD;
			case in_array(LegacyItemIds::typeIdToLegacyId($item->getTypeId()), [299, 303, 307, 311, 315, 749]):
				return self::SLOT_TORSO;
			case in_array(LegacyItemIds::typeIdToLegacyId($item->getTypeId()), [300, 304, 308, 312, 316, 750]):
				return self::SLOT_LEGS;
			case in_array(LegacyItemIds::typeIdToLegacyId($item->getTypeId()), [301, 305, 309, 313, 317, 751]):
				return self::SLOT_FEET;

			case $item instanceof Sword || $item->getBlockToolType() == BlockToolType::SWORD:
				return self::SLOT_SWORD;
			case $item instanceof Bow:
				return self::SLOT_BOW;

			case $item instanceof Hoe || $item->getBlockToolType() == BlockToolType::HOE:
				return self::SLOT_HOE;
			case ($item instanceof TieredTool && $item->getBlockToolType() == BlockToolType::SHEARS) || $item instanceof Shears:
				return self::SLOT_SHEARS;

			case $item instanceof Axe || $item->getBlockToolType() == BlockToolType::AXE:
				return self::SLOT_AXE;
			case $item instanceof Pickaxe || $item->getBlockToolType() == BlockToolType::PICKAXE:
				return self::SLOT_PICKAXE;
			case $item instanceof Shovel || $item->getBlockToolType() == BlockToolType::SHOVEL:
				return self::SLOT_SHOVEL;

			case $item instanceof Elytra:
				return self::SLOT_ELYTRA;

			case $item instanceof FishingRod || $item instanceof PMFishingRod:
				return self::SLOT_FISHING_ROD;

			default:
				return -1;
		}
	}

	public static function canEnchantWith(Item $item, Enchantment $enchantment): bool {
		$itype = self::typeToEtype(Utils::arrayToBitSet([self::getItemType($item)]));
		foreach ($itype as $fl) if ($enchantment->hasType($fl)) return true;
		return false;
	}

	public static function slotsToCategory(BitSet $slots): int {
		$slot = $slots->get(self::SLOT_ALL) ? self::SLOT_ALL
			: (($slots->get(self::SLOT_ARMOR) || $slots->get(self::SLOT_HEAD) || $slots->get(self::SLOT_TORSO) || $slots->get(self::SLOT_LEGS) || $slots->get(self::SLOT_FEET)) ? self::SLOT_ARMOR
				: (($slots->get(self::SLOT_TOOL) || $slots->get(self::SLOT_AXE) || $slots->get(self::SLOT_SHOVEL) || $slots->get(self::SLOT_HOE) || $slots->get(self::SLOT_SHEARS) || $slots->get(self::SLOT_FLINT_AND_STEEL) || $slots->get(self::SLOT_DIG)) ? self::SLOT_TOOL
					: ($slots->get(self::SLOT_SWORD) ? self::SLOT_SWORD
						: ($slots->get(self::SLOT_PICKAXE) ? self::SLOT_PICKAXE
							: ($slots->get(self::SLOT_FISHING_ROD) ? self::SLOT_FISHING_ROD
								: self::SLOT_NONE)))));
		return match ($slot) {
			self::SLOT_HEAD, self::SLOT_TORSO, self::SLOT_LEGS, self::SLOT_FEET, self::SLOT_ARMOR => self::CAT_ARMOR,
			self::SLOT_SWORD => self::CAT_SWORD,
			self::SLOT_AXE, self::SLOT_SHOVEL, self::SLOT_HOE, self::SLOT_TOOL, self::SLOT_DIG, self::SLOT_FLINT_AND_STEEL, self::SLOT_SHEARS => self::CAT_TOOL,
			self::SLOT_PICKAXE => self::CAT_PICKAXE,
			self::SLOT_FISHING_ROD => self::CAT_FISHING_ROD,
			default => self::CAT_UNIVERSAL
		};
	}

	public static function rarityColor(int $rarity): string {
		return match ($rarity) {
			1 => TextFormat::GREEN,
			2 => TextFormat::DARK_GREEN,
			3 => TextFormat::YELLOW,
			4 => TextFormat::GOLD,
			// 5 => TextFormat::DARK_PURPLE,	TODO: Mythic
			5 => TextFormat::RED,
			default => TextFormat::GRAY
		};
	}

	public static function rarityName(int $rarity): string {
		return match ($rarity) {
			1 => "Common",
			2 => "Uncommon",
			3 => "Rare",
			4 => "Legendary",
			5 => "Divine",
			default => "Unknown",
		};
	}

	// = = = = = [ VANILLA ENCHANTMENT IDS ] = = = = =

	const PROTECTION = 502;
	const FIRE_PROTECTION = 503;
	const FEATHER_FALLING = 504;
	const BLAST_PROTECTION = 505;
	const PROJECTILE_PROTECTION = 506;
	const THORNS = 5;
	const RESPIRATION = 6;
	const DEPTH_STRIDER = 7;
	const AQUA_AFFINITY = 8;
	const SHARPNESS = 9;
	const SMITE = 10;
	const BANE_OF_ARTHROPODS = 11;
	const KNOCKBACK = 12;
	const FIRE_ASPECT = 13;
	const LOOTING = 14;
	const EFFICIENCY = 501;
	const SILK_TOUCH = 16;
	const UNBREAKING = 500;
	const FORTUNE = 18;
	const POWER = 19;
	const PUNCH = 20;
	const FLAME = 21;
	const INFINITY = 22;
	const LUCK_OF_THE_SEA = 23;
	const LURE = 24;
	const FROST_WALKER = 25;
	const MENDING = 26;
	const BINDING = 27;
	const VANISHING = 28;
	const IMPALING = 29;
	const RIPTIDE = 30;
	const LOYALTY = 31;
	const CHANNELING = 32;

	// = = = = = [ PICKAXE ENCHANTMENT IDS ] = = = = =

	const AUTOSMELT = 1001;
	const FEATHER_WEIGHT = 1002;
	const FEED = 1003;
	const KEY_MAGNET = 1004;
	const KEY_RUSH = 1005;
	const MAGNIFY = 1006;
	const PURIFY = 1008;
	const SIFT = 1009;
	const TRANSFUSION = 1010;

	// = = = = = [ SWORD ENCHANTMENT IDS ] = = = = =

	const BACKSTAB = 2001;
	const BLEED = 2002;
	const COMBO = 2003;
	const DAZE = 2004;
	const DECAY = 2005;
	const ELECTRIFY = 2006;
	const EXECUTE = 2007;
	const GRIND = 2008;
	const HADES = 2009;
	const KABOOM = 2010;
	const KEY_THEFT = 2011;
	const OOF = 2012;
	const PIERCE = 2013;
	const RADIATION = 2014;
	const SHUFFLE = 2015;
	const SPITE = 2016;
	const STARVATION = 2017;
	const TECH_BLAST = 2018;
	const TIDES = 2019;
	const UPLIFT = 2020;
	const ZEUS = 2021;
	const LIFESTEAL = 2022;

	// = = = = = [ ROD ENCHANTMENT IDS ] = = = = =

	const FLING = 3001;
	const METAL_DETECTOR = 3002;
	const POSEIDON = 3003;
	const SUPER_GLUE = 3004;
	const THERMAL_HOOK = 3005;
	const SONAR = 3006;

	// = = = = = [ ARMOR ENCHANTMENT IDS ] = = = = =

	const ANTI_KNOCKBACK = 4001;
	const BLESSING = 4002;
	const BUNNY = 4003;
	const CROUCH = 4004;
	const DODGE = 4005;
	const ENDURANCE = 4006;
	const FIREBALL = 4007;
	const GEARS = 4008;
	const GLOWING = 4009;
	const GODLY_RETRIBUTION = 4010;
	const OBSIDIAN = 4012;
	const OVERLORD = 4013;
	const RAGE = 4014;
	const SNARE = 4015;
	const SORCERY = 4016;

	const ABSORB = 4017;
	const FORESIGHT = 4018;

	// = = = = = [ AXE ENCHANTMENT IDS ] = = = = =

	const LUMBERJACK = 4011; // change id to 7000
	const WORM = 7001;
	const DEBARKER = 7002;

	// = = = = = [ FARMING ENCHANTMENT IDS ] = = = = =

	const FERTILIZE = 5000;
	const HARVEST = 5001;
	const VENDOR = 5002;
	const CAPSULE = 5003;
	const BURROW = 5004;
	const TILLER = 5005;

	// = = = = = [ UNIVERSAL ENCHANTMENT IDS ] = = = = =

	const TRANSMUTATION = 6000;
}

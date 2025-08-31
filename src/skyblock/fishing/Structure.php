<?php

namespace skyblock\fishing;

/**
 * INFO
 * 
 * For the constant DATA_PERCENT
 * -1 = it has no set chance
 * -2 = it relies on the water & lava chances in the extra data.
 */
class Structure{

	public const TYPE_ITEM = "i";
	public const TYPE_KEY = "k";

	public const FISHING_WATER = 0;
	public const FISHING_LAVA = 1;

	public const CATEGORY_FISH = 0;
	public const CATEGORY_JUNK = 1;
	public const CATEGORY_TREASURE = 2;
	public const CATEGORY_RESOURCE = 3;

	public const RARITY_COMMON = 1;
	public const RARITY_UNCOMMON = 2;
	public const RARITY_RARE = 3;
	public const RARITY_LEGENDARY = 4;
	public const RARITY_DIVINE = 5;

	public const DATA_CATEGORY = "category"; // int
	public const DATA_RARITY = "rarity"; // int
	public const DATA_PERCENT = "percent"; // float
	public const DATA_WATER_EXCLUSIVE = "water_exclusive"; // bool
	public const DATA_LAVA_EXCLUSIVE = "lava_exclusive"; // bool
	public const DATA_EXTRA = "extra"; // array

	public const EXTRA_BROKEN_CHANCE = "broken_chance"; // float
	public const EXTRA_CHANCE_FOR_MAX = "chance_for_max"; // float
	public const EXTRA_MIN_DAMAGE = "min_damage"; // float DO NOT SET TO 100
	public const EXTRA_LEVEL_CHANCES = "level_chances"; // array w/ float
	public const EXTRA_WATER_CHANCE = "water_chance"; // float
	public const EXTRA_LAVA_CHANCE = "lava_chance"; // float

	public const FINDS = [
		"i:raw_cod" => [
			self::DATA_CATEGORY => self::CATEGORY_FISH,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:cooked_cod" => [
			self::DATA_CATEGORY => self::CATEGORY_FISH,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_LAVA_EXCLUSIVE => true
		],
		"i:raw_salmon" => [
			self::DATA_CATEGORY => self::CATEGORY_FISH,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:cooked_salmon" => [
			self::DATA_CATEGORY => self::CATEGORY_FISH,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_LAVA_EXCLUSIVE => true
		],
		"i:pufferfish" => [
			self::DATA_CATEGORY => self::CATEGORY_FISH,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
		],
		"i:clownfish" => [
			self::DATA_CATEGORY => self::CATEGORY_FISH,
			self::DATA_RARITY => self::RARITY_UNCOMMON
		],
		"i:fishing_rod" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 75.0
			]
		],
		"i:bow" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 75.0
			]
		],
		"i:brick" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:bowl" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON
		],
		"i:lily_pad" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:rose" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:blue_orchid" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:prismarine_crystals" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:prismarine_shard" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:golden_apple" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_PERCENT => 35.0
		],
		"i:enchanted_golden_apple" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_WATER_CHANCE => 1.65,
				self::EXTRA_LAVA_CHANCE => 4.75
			]
		],
		"k:iron" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_WATER_CHANCE => 40,
				self::EXTRA_LAVA_CHANCE => 12
			]
		],
		"k:gold" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_WATER_CHANCE => 40,
				self::EXTRA_LAVA_CHANCE => 12
			]
		],
		"k:diamond" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_WATER_CHANCE => 12,
				self::EXTRA_LAVA_CHANCE => 35
			]
		],
		"k:emerald" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_WATER_CHANCE => 10.0,
				self::EXTRA_LAVA_CHANCE => 30.0
			]
		],
		"k:divine" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_WATER_CHANCE => 0.015,
				self::EXTRA_LAVA_CHANCE => 0.075
			]
		],
		"i:common_redeemed_book" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_CHANCE_FOR_MAX => 35.5,
				self::EXTRA_WATER_CHANCE => 45.0,
				self::EXTRA_LAVA_CHANCE => 2.5
			]
		],
		"i:uncommon_redeemed_book" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_CHANCE_FOR_MAX => 12.35,
				self::EXTRA_WATER_CHANCE => 25.0,
				self::EXTRA_LAVA_CHANCE => 7.5
			]
		],
		"i:rare_redeemed_book" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_CHANCE_FOR_MAX => 7.25,
				self::EXTRA_WATER_CHANCE => 15.0,
				self::EXTRA_LAVA_CHANCE => 25.0
			]
		],
		"i:legendary_redeemed_book" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_CHANCE_FOR_MAX => 5.5,
				self::EXTRA_WATER_CHANCE => 7.5,
				self::EXTRA_LAVA_CHANCE => 35.0
			]
		],
		"i:divine_redeemed_book" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_PERCENT => -2,
			self::DATA_EXTRA => [
				self::EXTRA_CHANCE_FOR_MAX => 2.5,
				self::EXTRA_WATER_CHANCE => 0.01,
				self::EXTRA_LAVA_CHANCE => 0.05
			]
		],
		"i:leather_boots" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 10,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 75.0
			]
		],
		"i:leather_leggings" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 10,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 75.0
			]
		],
		"i:leather_tunic" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 10,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 75.0
			]
		],
		"i:leather_cap" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 10,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 75.0
			]
		],
		"i:chain_boots" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 7.5,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 97.5
			]
		],
		"i:chain_leggings" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 7.5,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 97.5
			]
		],
		"i:chain_chestplate" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 7.5,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 97.5
			]
		],
		"i:chain_helmet" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 7.5,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 97.5
			]
		],
		"i:gold_boots" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 97.5
			]
		],
		"i:gold_leggings" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 97.5
			]
		],
		"i:gold_chestplate" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 97.5
			]
		],
		"i:gold_helmet" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5,
			self::DATA_WATER_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_BROKEN_CHANCE => 97.5
			]
		],
		"i:iron_boots" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 10,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:iron_leggings" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 10,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:iron_chestplate" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 10,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:iron_helmet" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 10,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:diamond_boots" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:diamond_leggings" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:diamond_chestplate" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:diamond_helmet" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:netherite_boots" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 2.25,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:netherite_leggings" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 2.25,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:netherite_chestplate" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 2.25,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:netherite_helmet" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 2.25,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_EXTRA => [
				self::EXTRA_MIN_DAMAGE => 99.5
			]
		],
		"i:stick" => [
			self::DATA_CATEGORY => self::CATEGORY_JUNK,
			self::DATA_RARITY => self::RARITY_COMMON,
			self::DATA_PERCENT => 5.75,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:rotten_flesh" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_COMMON
		],
		"i:bone" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_COMMON
		],
		"i:skeleton_skull" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_PERCENT => 12.33,
			self::DATA_WATER_EXCLUSIVE => true
		],
		"i:wither_skeleton_skull" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_PERCENT => 12.33,
			self::DATA_LAVA_EXCLUSIVE => true
		],
		"i:string" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_COMMON
		],
		"i:gunpowder" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_UNCOMMON
		],
		"i:blaze_rod" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_LAVA_EXCLUSIVE => true
		],
		"i:nether_wart" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_LAVA_EXCLUSIVE => true
		],
		"i:blaze_powder" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_LAVA_EXCLUSIVE => true
		],
		"i:nether_quartz" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_LAVA_EXCLUSIVE => true
		],
		"i:nether_brick" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_LAVA_EXCLUSIVE => true,
		],
		"i:amethyst_shard" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_UNCOMMON,
			self::DATA_LAVA_EXCLUSIVE => true,
		],
		"i:withered_bone" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_PERCENT => 11.55,
		],
		"i:white_mushroom" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_PERCENT => 11.55,
		],
		"i:jewel_of_the_end" => [
			self::DATA_CATEGORY => self::CATEGORY_RESOURCE,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_PERCENT => 11.55,
		],
		"i:vertical_extender" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_PERCENT => 0.7535,
			self::DATA_EXTRA => [
				self::EXTRA_LEVEL_CHANCES => [
					1 => 75,
					2 => 25
				]
			]
		],
		"i:horizontal_extender" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_PERCENT => 0.6355,
			self::DATA_EXTRA => [
				self::EXTRA_LEVEL_CHANCES => [
					1 => 75.75,
					2 => 24.25
				]
			]
		],
		"i:solidifier" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_PERCENT => 1.4503,
			self::DATA_EXTRA => [
				self::EXTRA_LEVEL_CHANCES => [
					1 => 45.0,
					2 => 70.0,
					3 => 85.0,
					4 => 95.0,
					5 => 100.0
				]
			]
		],
		"i:pet_key" => [
			self::DATA_CATEGORY => self::CATEGORY_TREASURE,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_LAVA_EXCLUSIVE => true,
			self::DATA_PERCENT => 0.8875
		],
	];
}
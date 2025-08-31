<?php namespace skyblock\shop;

use skyblock\generators\tile\OreGenerator;

class ShopPrices{

	// [buy price, sell price, icon, custom name, extra data]
	const SHOP_ITEMS = [
		1 => [
			"water_bucket" => [3000, -1, "326-0.png"],
			"lava_bucket" => [3000, -1, "327-0.png"],
			"bread" => [5, -1],
			"bone_meal" => [25],
			"sugarcane" => [-1, 5], // CROP
			"cactus" => [-1, 10], // CROP
			"gravel" => [10, -1],
			"oak_log" => [-1, 1], // LOG
			"stripped_oak_log" => [-1, 2], // LOG
			"cobblestone" => [-1, 1],
			"arrow" => [12, 6],
			"flower_pot" => [10],
			"poppy" => [75, 75],
			"blue_orchid" => [5],
			"allium" => [5],
			"pink_stained_clay" => [30],
			"magenta_stained_clay" => [30],
			"cyan_stained_clay" => [30],
			"lime_stained_clay" => [30],
			"light_blue_stained_clay" => [30],
		],
		2 => [
			"sand" => [50],
			"red_sand" => [50],
			"clay" => [2],
			"stained_clay" => [30],
			"black_stained_clay" => [30],
			"gray_stained_clay" => [30],
			"light_gray_stained_clay" => [30],
			"brown_stained_clay" => [30],
			"stained_glass" => [20],
			"black_stained_glass" => [20],
			"gray_stained_glass" => [20],
			"light_gray_stained_glass" => [20],
			"brown_stained_glass" => [20],
			"coal" => [-1, 4], // RESOURCE
			"coal_block" => [36], // RESOURCE BLOCK
			"ore_generator" => [2500, -1, "custom/oregen.png", "", [OreGenerator::TYPE_COAL, 1]], // GEN
		],
		3 => [
			"oak_sapling" => [10, -1], // SAPLING
			"spruce_log" => [-1, 2], // LOG
			"stripped_spruce_log" => [-1, 4], // LOG
			"dirt" => [20],
			"stone" => [30],
			"lightning_rod" => [60],
			"azure_bluet" => [-1, 27],
			"red_tulip" => [5],
			"orange_tulip" => [5],
			"pink_stained_glass" => [30],
			"magenta_stained_glass" => [30],
			"light_blue_stained_glass" => [30],
			"lime_stained_glass" => [30],
			"cyan_stained_glass" => [30],
			"wheat_seeds" => [45, -1], // CROP SEEDS
			"wheat" => [-1, 15], // CROP
			"raw_porkchop" => [-1, 1],
			"raw_chicken" => [3],
			"raw_mutton" => [-1, 4],
			"raw_beef" => [-1, 5],
			"iron_ingot" => [-1, 8], // RESOURCE
			"iron_block" => [-1, 72], // RESOURCE BLOCK
			"ore_generator" => [7500, -1, "custom/oregen.png", "", [OreGenerator::TYPE_IRON, 1]], // GEN
		],
		4 => [
			"spruce_sapling" => [20], // SAPLING
			"birch_log" => [-1, 3], // LOG
			"stripped_birch_log" => [-1, 6], // LOG
			"apple" => [10],
			"cookie" => [1],
			"cobweb" => [30],
			"stone_bricks" => [40],
			"brick" => [50],
			"pearlescent_froglight" => [75, -1, "", "Pearlescent Froglight"],
			"verdant_froglight" => [75, -1, "", "Verdant Froglight"],
			"ochre_froglight" => [75, -1, "", "Ochre Froglight"],
			"red_stained_clay" => [50],
			"orange_stained_clay" => [50],
			"yellow_stained_clay" => [50],
			"green_stained_clay" => [50],
			"blue_stained_clay" => [50],
			"purple_stained_clay" => [50],
			"mycelium" => [200, 100],
			"hopper" => [100],
			"mob_spawner" => [10000],

			"cooked_porkchop" => [4, 1],

			"cooked_chicken" => [5, 3],
			"feather" => [-1, 4],

			"cooked_mutton" => [6, 4],
			"white_wool" => [10, 7],

			"cooked_beef" => [7, 5],
			"leather" => [-1, 9],

			"spider_eye" => [-1, 12],
			"string" => [-1, 7],

			"bone" => [-1, 12],
			"skeleton_skull" => [-1, 40],

			"rotten_flesh" => [-1, 15],
			"zombie_head" => [-1, 60],

			"wither_skeleton_skull" => [-1, 80],
			"wither_rose" => [-1, 20],
			"withered_bone" => [-1, 35],

			"blaze_rod" => [-1, 25],

			"gunpowder" => [-1, 18],
			"creeper_head" => [-1, 70],
			"disc_fragment_5" => [-1, 100],
			"white_mushroom" => [-1, 45],
			"potato" => [50, 18], // CROP
		],
		5 => [
			"birch_sapling" => [30],
			"jungle_log" => [-1, 4], // LOG
			"stripped_jungle_log" => [-1, 8], // LOG
			"carrot" => [65, 20], // CROP
			"mossy_cobblestone" => [30],
			"grass" => [15],
			"rooted_dirt" => [20, -1, "", "Rooted Dirt"],
			"granite" => [50],
			"white_tulip" => [5],
			"pink_tulip" => [5],
			"oxeye_daisy" => [5],
			"chain" => [80],
			"azalea_leaves" => [30],
			"flowering_azalea_leaves" => [50],
			"mangrove_leaves" => [50],
			"mangrove_roots" => [60],
			"red_stained_glass" => [50],
			"orange_stained_glass" => [50],
			"yellow_stained_glass" => [50],
			"green_stained_glass" => [50],
			"blue_stained_glass" => [50],
			"purple_stained_glass" => [50],
			"orange_concrete_powder" => [100],
			"light_gray_concrete_powder" => [100],
			"pink_concrete_powder" => [100],
			"brown_concrete_powder" => [100],
			"ender_chest" => [1000],
			"redstone_dust" => [-1, 4], // RESOURCE
			"redstone_block" => [-1, 36], // RESOURCE BLOCK
			"ore_generator" => [12500, -1, "custom/oregen.png", "", [OreGenerator::TYPE_REDSTONE, 1]], // GEN
			"armor_stand" => [5000, -1, "416-0.png"],
			"item_frame" => [200],
		],
		6 => [
			"jungle_sapling" => [40],
			"acacia_log" => [-1, 5/**.50*/],
			"stripped_acacia_log" => [-1, 10], // LOG
			"beetroot" => [-1, 25],
			"beetroot_seeds" => [2],
			"vines" => [20],
			"lily_pad" => [20],
			"tinted_glass" => [30],
			"sponge" => [10],
			"shroomlight" => [50],
			"basalt" => [25],
			"podzol" => [100],
			"cracked_stone_bricks" => [40],
			"podzol" => [40],
			"diorite" => [50],
			"white_wool" => [8, 8],
			"black_wool" => [8, 8],
			"gray_wool" => [8, 8],
			"light_gray_wool" => [8, 8],
			"brown_wool" => [8, 8],
			"white_concrete_powder" => [125],
			"magenta_concrete_powder" => [10],
			"light_blue_concrete_powder" => [10],
			"yellow_concrete_powder" => [10],
			"lime_concrete_powder" => [10],
			"gray_concrete_powder" => [10],
			"cyan_concrete_powder" => [10],
			"purple_concrete_powder" => [10],
			"blue_concrete_powder" => [10],
			"green_concrete_powder" => [10],
			"red_concrete_powder" => [10],
			"black_concrete_powder" => [10],
			"sugar" => [-1, 20],
			"glass_bottle" => [-1, 2],
			"breeze_rod" => [-1, 60],
			"lapis_lazuli" => [-1, 6], // RESOURCE
			"lapis_lazuli_block" => [-1, 54], // RESOURCE BLOCK
			"ore_generator" => [20000, -1, "custom/oregen.png", "", [OreGenerator::TYPE_LAPIS_LAZULI, 1]], // GEN
		],
		7 => [
			"acacia_sapling" => [50],
			"dark_oak_log" => [-1, 6],
			"stripped_dark_oak_log" => [-1, 12], // LOG
			"pumpkin" => [-1, 40], // CROP
			"pumpkin_seeds" => [85, -1], // CROP
			"shears" => [20],
			"quartz_block" => [100],
			"nether_bricks" => [50],
			"glowstone_dust" => [10],
			"netherrack" => [-1, 5],
			"glowstone_block" => [-1, 7],
			"nether_brick" => [-1, 12],
			"quartz" => [-1, 15],
			"warped_wart_block" => [25],
			"twisting_vines" => [25],
			"weeping_vines" => [25],
			"cave_vines" => [25],
			
			"andesite" => [50],
			"amethyst_shard" => [-1, 25],
			"amethyst_cluster" => [50],
			"small_amethyst_bud" => [100],
			"medium_amethyst_bud" => [100],
			"large_amethyst_bud" => [100],
			"budding_amethyst" => [200],
			"amethyst_block" => [200],
			"pink_wool" => [8, 8],
			"magenta_wool" => [8, 8],
			"light_blue_wool" => [8, 8],
			"cyan_wool" => [8, 8],
			"lime_wool" => [8, 8],
			"orange_concrete" => [15],
			"light_gray_concrete" => [15],
			"pink_concrete" => [15],
			"brown_concrete" => [15],
			"copper_ingot" => [-1, 12], // RESOURCE
			"copper_block" => [-1, 108], // RESOURCE BLOCK
			"ore_generator" => [37500, -1, "custom/oregen.png", "", [OreGenerator::TYPE_COPPER, 1]], // GEN
		],
		8 => [
			"dark_oak_sapling" => [60],
			"melon" => [-1, 10], // CROP
			"melon_block" => [-1, 90], // CROP BLOCK
			"melon_seeds" => [100], // CROP SEEDS
			"snowball" => [10],
			"bookshelf" => [100],
			"slime_block" => [300],
			"sculk" => [100],
			"calcite" => [20],
			"tuff" => [40],

			"candle" => [40],
			"deepslate" => [50],
			"cobbled_deepslate" => [50],

			"red_wool" => [8, 8],
			"orange_wool" => [8, 8],
			"yellow_wool" => [8, 8],
			"green_wool" => [8, 8],
			"blue_wool" => [8, 8],
			"purple_wool" => [8, 8],
			"white_concrete" => [15],
			"magenta_concrete" => [15],
			"light_blue_concrete" => [15],
			"yellow_concrete" => [15],
			"lime_concrete" => [15],
			"pink_concrete" => [15],
			"gray_concrete" => [15],
			"purple_concrete" => [15],
			"blue_concrete" => [15],
			"green_concrete" => [15],
			"red_concrete" => [15],
			"black_concrete" => [15],
			"gold_nugget" => [-1, 2], // RESOURCE
			"gold_ingot" => [-1, 18], // RESOURCE
			"gold_block" => [-1, 162], // RESOURCE BLOCK
			"ore_generator" => [50000, -1, "custom/oregen.png", "", [OreGenerator::TYPE_GOLD, 1]], // GEN
		],
		9 => [
			"end_rod" => [200],
			"nether_wart" => [300, 35], // CROP
			"soul_sand" => [20],
			"soul_soil" => [35],
			"packed_ice" => [200],
			"prismarine" => [200],
			"end_stone" => [100, 20],
			"blackstone" => [20],
			"gilded_blackstone" => [40],
			"red_dye" => [5],
			"yellow_dye" => [5],
			"ink_sac" => [5],
			"ender_eye" => [-1, 60, "", "Eye of Ender"],
			"ender_pearl" => [-1, 80, "", "Ender Pearl"],
			"jewel_of_the_end" => [-1, 1150, "", "Jewel of the End"],
		],
		10 => [
			"bamboo" => [500, 55], // CROP
			"end_stone_bricks" => [-1, 7],
			"dark_prismarine" => [20],
			"prismarine_bricks" => [20],
			"diamond" => [-1, 25], // RESOURCE
			"diamond_block" => [-1, 225], // RESOURCE BLOCK
			"ore_generator" => [75000, -1, "custom/oregen.png", "", [OreGenerator::TYPE_DIAMOND, 1]], // GEN
			"autominer" => [100000, -1, "custom/autominer.png"],
		],
		11 => [
			"purpur_block" => [100, 15],
			"purpur_pillar" => [100, 15],
			"red_mushroom" => [750, 15], // CROP
			"emerald" => [-1, 35], // RESOURCE
			"emerald_block" => [-1, 315], // RESOURCE BLOCK
			"ore_generator" => [100000, -1, "custom/oregen.png", "", [OreGenerator::TYPE_EMERALD, 1]], // GEN
		],
		12 => [
			"brown_mushroom" => [800, 20], // CROP
			"mangrove_log" => [65],
			"stripped_mangrove_log" => [-1, 130], // LOG
			"mangrove_roots" => [25],
			"sea_lantern" => [200],
			"obsidian" => [500, 45], // RESOURCE
			"polished_obsidian" => [-1, 405], // RESOURCE BLOCK
			"ore_generator" => [100000, -1, "custom/oregen.png", "", [OreGenerator::TYPE_OBSIDIAN, 1]], // GEN
		],
		13 => [
			"chorus_flower" => [1500, 5], // CROP SEEDS
			"chorus_fruit" => [-1, 75], // CROP
			"bone_block" => [500],
			"glow_ink_sac" => [60, -1],
			"big_dripleaf" => [30, -1],
			"small_dripleaf" => [15, -1],
			"glowing_obsidian" => [-1, 60, "246-0.png"], // RESOURCE
			"polished_glowing_obsidian" => [-1, 540], // RESOURCE BLOCK
			"ore_generator" => [100000, -1, "custom/oregen.png", "", [OreGenerator::TYPE_GLOWING_OBSIDIAN, 1]], // GEN
		],
		14 => [
			"magma_cream" => [-1, 7],
			"magma" => [1000],
			"experience_bottle" => [1000],
			"crying_obsidian" => [600],
		],
		15 => [
			"warped_stem" => [35],
			"crimson_stem" => [35],
			"warped_wart_block" => [35],
			"nether_wart_block" => [35],
			"ghast_tear" => [-1, 10],
			"prismarine_shard" => [-1, 10],
			"prismarine_crystals" => [-1, 6],
			"elytra" => [30000, -1, "443-0.png", "Elytra"],
			"spyglass" => [10000],
			"dimensional_block" => [150000, -1, "custom/dimensional.png", "", [1]],
			"ancient_debris" => [-1, 80], // RESOURCE
			"netherite_block" => [-1, 720], // RESOURCE BLOCK
			"firework_rocket" => [75],
		],
		16 => [
			"cherry_leaves" => [50, -1],
			"cherry_log" => [50, -1],
			"pink_petals" => [50, -1],
			"mud" => [15, -1],
			"reinforced_deepslate" => [200, -1],
			"honeycomb_block" => [40, -1],
			"honeycomb" => [10, -1],
			"gilded_obsidian" => [-1, 100], // RESOURCE
			"polished_gilded_obsidian" => [-1, 900], // RESOURCE BLOCK
		],
		17 => [

		],
		18 => [

		],
		19 => [

		],
		20 => [

		]
	];

}
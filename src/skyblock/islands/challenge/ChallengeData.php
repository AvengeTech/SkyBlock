<?php namespace skyblock\islands\challenge;

use core\utils\TextFormat as TF;

class ChallengeData{

	const DIFFICULTY_EASY = 1;
	const DIFFICULTY_NORMAL = 2;
	const DIFFICULTY_HARD = 3;

	/** Nice to look at in array ;-; */
	const LEVEL_1 = 1;
	const LEVEL_2 = 2;
	const LEVEL_3 = 3;
	const LEVEL_4 = 4;
	const LEVEL_5 = 5;
	const LEVEL_6 = 6;
	const LEVEL_7 = 7;
	const LEVEL_8 = 8;
	const LEVEL_9 = 9;
	const LEVEL_10 = 10;
	const LEVEL_11 = 11;
	const LEVEL_12 = 12;
	const LEVEL_13 = 13;
	const LEVEL_14 = 14;
	const LEVEL_15 = 15;
	const LEVEL_16 = 16;
	const LEVEL_17 = 17;
	const LEVEL_18 = 18;
	const LEVEL_19 = 19;
	const LEVEL_20 = 20;

	const CHALLENGES = [
		self::LEVEL_1 => [
			self::ISLAND_EXPAND => [
				"name" => "Upgrade Island",
				"description" => "Upgrade your island",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "IslandExpandChallenge",
				"progress" => [],
			],
			self::FURNACE_CRAFT => [
				"name" => "Hot!",
				"description" => "Craft a furnace",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftFurnaceChallenge",
				"progress" => [],
			],
			self::BED_CRAFT => [
				"name" => "Bedtime",
				"description" => "Craft a bed",
				"techits" => 75,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftBedChallenge",
				"progress" => [],
			],
			self::PLANT_SUGARCANE => [
				"name" => "Sugarcane farm",
				"description" => "Plant 5 Sugarcane",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantSugarcaneChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5]
				],
			],
			self::PLANT_CACTUS => [
				"name" => "Prickly Prickles",
				"description" => "Plant 5 cacti",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantCactusChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5]
				],
			],
			self::TRAPDOOR_CRAFT => [
				"name" => "Trap",
				"description" => "Craft 10 trapdoors",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftTrapdoorChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				],
			],
			self::COLLECT_FISH_1 => [
				"name" => "Fishies",
				"description" => "Collect 5 of any kind of fish from Fishing",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "CollectFish1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 5],
				],
			],
			self::MINE_COBBLESTONE_1 => [
				"name" => "Peach cobbler",
				"description" => "Mine 250 cobblestone",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BreakCobblestone1Challenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 250],
				]
			],
			self::BREAK_WOOD_1 => [
				"name" => "Can I AXE you a question",
				"description" => "Break 50 Oak Logs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BreakWood1Challenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 50],
				],
			],
			self::COLLECT_IRON_KEYS_1 => [
				"name" => "Iron Lock",
				"description" => "Collect 5 iron keys",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectIronKeys1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 5],
				],
			]
		],
		self::LEVEL_2 => [
			self::BREAK_WOOD_2 => [
				"name" => "Lumberjack Pro",
				"description" => "Break 100 Oak Logs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BreakWood2Challenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 100],
				],
			],
			self::MINE_COBBLESTONE_2 => [
				"name" => "Cobble cobble cobble",
				"description" => "Mine 500 cobblestone",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BreakCobblestone2Challenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 500],
				]
			],
			self::COBBLESTONE_OAK_STAIR => [
				"name" => "McFallen!",
				"description" => "Craft 4 cobblestone stairs and 4 oak wood stairs",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftCobblestoneOakStairChallenge",
				"progress" => [
					"cobblestone" => ["progress" => 0, "needed" => 4],
					"wood" => ["progress" => 0, "needed" => 4],
				],
			],
			self::COBBLESTONE_SLAB_CRAFT => [
				"name" => "Cobblestone Construction",
				"description" => "Craft 10 Cobblestone Slabs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftCobblestoneSlabChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				],
			],
			self::BUTTON_CRAFT => [
				"name" => "Bootons",
				"description" => "Craft 10 stone buttons",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftButtonChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				]
			],
			self::CRAFT_BRICKS => [
				"name" => "Three Little Pigs",
				"description" => "Craft a stack of Brick Blocks",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftBricksChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 64],
				],
			],
			self::CRAFT_PANES => [
				"name" => "Windows",
				"description" => "Craft 16 glass panes",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftPanesChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				]
			],
			self::COLLECT_SUGARCANE => [
				"name" => "Raising Canes",
				"description" => "Collect 32 sugar cane",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectSugarcaneChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 32],
				],
			],
			self::COLLECT_CACTUS => [
				"name" => "Prickling Pro",
				"description" => "Collect 32 cacti",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectCactusChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 32],
				],
			],
			self::BUY_COAL_GEN => [
				"name" => "Black Diamonds!",
				"description" => "Purchase a coal generator",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyCoalGeneratorChallenge",
				"progress" => [],
			],
			self::UPGRADE_COAL_GEN_1 => [
				"name" => "Dassalotta co!",
				"description" => "Upgrade a coal ore generator to level 2",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "UpgradeCoalGenerator1Challenge",
				"progress" => []
			],
			self::CRAFT_COAL_BLOCKS => [
				"name" => "Industrial",
				"description" => "Craft 16 coal blocks",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftCoalBlocksChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				],
			],
			self::APPLY_ENCHANTMENT_1 => [
				"name" => "Enchanted!",
				"description" => "Apply 1 enchantment to any item.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "ApplyEnchantment1Challenge",
				"progress" => [],
			],
			self::COLLECT_GOLD_KEYS_1 => [
				"name" => "14 Karat Keys",
				"description" => "Collect 10 gold keys",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectGoldKeys1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 10],
				],
			]
		],
		self::LEVEL_3 => [
			self::CRAFT_CHEST => [
				"name" => "Storage",
				"description" => "Craft 16 chests",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftChestChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				]
			],
			self::PLANT_WHEAT => [
				"name" => "Novice Farmer",
				"description" => "Plant 16 Wheat Seeds",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantWheatChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 16],
				],
			],
			self::COLLECT_WHEAT => [
				"name" => "Agriculture",
				"description" => "Collect 32 Wheat",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectWheatChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 32],
				]
			],
			self::CRAFT_FENCE => [
				"name" => "Fence Protection",
				"description" => "Craft 20 fences",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftFenceChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 20],
				]
			],
			self::CRAFT_GATE => [
				"name" => "Gate Protection",
				"description" => "Craft 4 fence gates",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftGateChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 4],
				]
			],
			self::PLANT_OAK_SAPLING => [
				"name" => "Tree Saver",
				"description" => "Plant 10 Oak Saplings",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantOakSaplingChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 10],
				],
			],
			self::CRAFT_TORCH => [
				"name" => "Lights on!",
				"description" => "Craft 32 Torches",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftTorchChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 32],
				]
			],
			self::CRAFT_SIGN => [
				"name" => "Organizing",
				"description" => "Craft 10 Signs",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftSignChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				]
			],
			self::CRAFT_GREEN_WOOL => [
				"name" => "Greens",
				"description" => "Craft 10 Green Wool",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftGreenWoolChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				]
			],
			self::COLLECT_FISH_2 => [
				"name" => "Fishering",
				"description" => "Collect 10 of any kind of fish from Fishing",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectFish2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 10],
				],
			],
			self::BUY_IRON_GEN => [
				"name" => "Iron Man",
				"description" => "Buy an iron generator",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyIronGeneratorChallenge",
				"progress" => [],
			],
			self::UPGRADE_IRON_GEN_1 => [
				"name" => "Better Iron?",
				"description" => "Upgrade an iron generator",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "UpgradeIronGen1Challenge",
				"progress" => [],
			],
			self::CRAFT_IRON_BLOCKS => [
				"name" => "Iron Golem",
				"description" => "Craft 16 Iron Blocks",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftIronBlocksChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				]
			],
			self::CRAFT_IRON_NUGGETS => [
				"name" => "Iron Nuggets",
				"description" => "Craft 16 Iron Nuggets",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftIronNuggetsChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				]
			],
			self::COLLECT_DIAMOND_KEYS_1 => [
				"name" => "Iced Out Keys",
				"description" => "Collect 15 diamond keys",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectDiamondKeys1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 15],
				],
			]
		],
		self::LEVEL_4 => [
			self::COLLECT_SPRUCE_1 => [
				"name" => "Spruce 50",
				"description" => "Collect 50 spruce logs",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectSpruceChallenge1",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::CRAFT_BREAD => [
				"name" => "Baguette!",
				"description" => "Craft 10 bread",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftBreadChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				]
			],
			self::CRAFT_STONE_BRICKS => [
				"name" => "Stone Bricks",
				"description" => "Craft 64 stone bricks",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftStoneBricksChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 64],
				]
			],
			self::CRAFT_WHITE_WOOL => [
				"name" => "Clouds",
				"description" => "Craft 10 white wool",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftWhiteWoolChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				]
			],
			self::CRAFT_PAINTINGS => [
				"name" => "Visual",
				"description" => "Craft 5 paintings",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftPaintingsChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 5],
				]
			],
			self::CRAFT_LADDERS => [
				"name" => "Going Up",
				"description" => "Craft 9 ladders",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftLaddersChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 9],
				]
			],
			self::CRAFT_BOW => [
				"name" => "Bowing",
				"description" => "Craft a bow",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftBowChallenge",
				"progress" => []
			],
			self::BONEMEAL_SAPLINGS => [
				"name" => "Ez Growth",
				"description" => "Bonemeal 10 Saplings",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BonemealSaplingsChallenge",
				"progress" => [
					"saplings" => ["progress" => 0, "needed" => 10],
				]
			],
			self::COLLECT_POTATOES => [
				"name" => "Yam Yam!",
				"description" => "Collect 32 potatoes",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectPotatoesChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 32],
				]
			],
			self::UPGRADE_MOB_SPAWNER_1 => [
				"name" => "Hostile Upgrade",
				"description" => "Upgrade a mob spawner to level 2",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "UpgradeMobSpawner1Challenge",
				"progress" => [],
			],
			self::KILL_PIGS_1 => [
				"name" => "Oink-Oink!",
				"description" => "Kill 25 Pigs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "KillPigs1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_CHICKENS_1 => [
				"name" => "Kickin' Chicken",
				"description" => "Kill 25 Chickens",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "KillChickens1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_SHEEP_1 => [
				"name" => "Sheep Slayer",
				"description" => "Kill 25 Sheep",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "KillSheep1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_COWS_1 => [
				"name" => "Moo Moo Murderer",
				"description" => "Kill 25 Cows",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "KillCows1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::REFINE_ESSENCE_1 => [
				"name" => "Refined!",
				"description" => "Refine 10 essence of any type.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "RefineEssence1Challenge",
				"progress" => [
					"refined" => ["progress" => 0, "needed" => 10],
				]
			],
			self::COLLECT_EMERALD_KEYS_1 => [
				"name" => "Village Secrets?",
				"description" => "Collect 15 emerlad keys",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "CollectEmeraldKeys1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 15],
				],
			]
		],
		self::LEVEL_5 => [
			self::COLLECT_SPRUCE_2 => [
				"name" => "Spruce 200",
				"description" => "Collect 200 spruce logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectSpruce2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 200],
				]
			],
			self::COLLECT_JUNGLE_1 => [
				"name" => "Jungle 50",
				"description" => "Collect 50 Jungle Logs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectJungle1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::CRAFT_POLISHED_GRANITE => [
				"name" => "Polished Granite",
				"description" => "Craft 64 blocks of Polished Granite",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftPolishedGraniteChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 64],
				]
			],
			self::GROW_BIRCH_SAPLINGS => [
				"name" => "Birch Field",
				"description" => "Grow 10 Birch Saplings with bonemeal",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "GrowBirchSaplingsChallenge",
				"progress" => [
					"grown" => ["progress" => 0, "needed" => 10],
				]
			],
			self::COLLECT_FISH_3 => [
				"name" => "Getting Fishy",
				"description" => "Collect 15 of any kind of fish from Fishing",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectFish3Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 15],
				],
			],
			self::SELL_CARROTS => [
				"name" => "Feed the rabbits",
				"description" => "Sell 16 carrots",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "SellCarrotsChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 16],
				]
			],
			self::KILL_PIGS_2 => [
				"name" => "Squeal!",
				"description" => "Kill 100 Pigs",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillPigs2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_CHICKENS_2 => [
				"name" => "What The Cluck!?",
				"description" => "Kill 100 Chicken",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillChickens2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_SHEEP_2 => [
				"name" => "Holy Sheep!",
				"description" => "Kill 100 Sheep",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillSheep2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_COWS_2 => [
				"name" => "MOOODER!",
				"description" => "Kill 100 Cows",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillCows2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::BUY_REDSTONE_GEN => [
				"name" => "Power up!",
				"description" => "Purchase a Redstone Ore Generator",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyRedstoneGeneratorChallenge",
				"progress" => []
			],
			self::UPGRADE_REDSTONE_GEN_1 =>[
				"name" => "Reddy For Stone",
				"description" => "Upgrade a Redstone Ore Generator twice!",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeRedstoneGenerator1Challenge",
				"progress" => [
					"upgrades" => ["progress" => 0, "needed" => 2]
				]
			],
			self::CRAFT_REDSTONE_BLOCKS => [
				"name" => "Redstone",
				"description" => "Craft 16 Redstone Blocks",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftRedstoneBlocksChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				],
			],
			self::UNLOCK_PET_BOX => [
				"name" => "Whats In The Box?",
				"description" => "Unlock a pet box!",
				"techits" => 750,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UnlockPetBoxChallenge",
				"progress" => [],
			],
			self::APPLY_ENCHANTMENT_2 => [
				"name" => "Shinyyy!",
				"description" => "Apply 5 enchantments to any item.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "ApplyEnchantment2Challenge",
				"progress" => [
					"applied" => ["progress" => 0, "needed" => 5],
				],
			]
		],
		self::LEVEL_6 => [
			self::COLLECT_JUNGLE_2 => [
				"name" => "Jungle 200",
				"description" => "Collect 200 Jungle Logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectJungle2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 200],
				]
			],
			self::COLLECT_ACACIA_1 => [
				"name" => "Acacia 50",
				"description" => "Collect 50 Acacia Logs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectAcacia1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::CRAFT_POLISHED_DIORITE => [
				"name" => "Dio's Rite",
				"description" => "Craft 64 blocks of Polished Diorite",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftPolishedDioriteChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 64],
				]
			],
			self::PLANT_JUNGLE_SAPLINGS => [
				"name" => "Jumanji",
				"description" => "Plant 10 Jungle Saplings",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantJungleSaplingsChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 10],
				],
			],
			self::PLACE_VINES => [
				"name" => "Do it for the Vine",
				"description" => "Plant 8 Vines on your island",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlaceVinesChallenge",
				"progress" => [
					"placed" => ["progress" => 0, "needed" => 8],
				],
			],
			self::CRAFT_CARPET => [
				"name" => "It's So Fluffy!",
				"description" => "Craft 10 carpet",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftCarpetChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				]
			],
			self::PLANT_BEETROOT => [
				"name" => "Sick Beets!",
				"description" => "Plant 15 beetroot seeds",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantBeetrootChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 15]
				]
			],
			self::CRAFT_BEETROOT_SOUP => [
				"name" => "Soup",
				"description" => "Craft 10 beetroot soup",
				"techits" => 50,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftBeetrootSoupChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 10],
				]
			],
			self::BUY_LAPIS_GEN => [
				"name" => "Fake Diamonds",
				"description" => "Purchase a Lapis Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyLapisGeneratorChallenge",
				"progress" => []
			],
			self::UPGRADE_LAPIS_GEN => [
				"name" => "Bluer Pickles!",
				"description" => "Upgrade a Lapis Ore Generator twice!",
				"techits" => 750,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeLapisGenerator1Challenge",
				"progress" => [
					"upgrades" => ["progress" => 0, "needed" => 2]
				]
			],
			self::CRAFT_LAPIS_BLOCKS => [
				"name" => "Lazuli",
				"description" => "Craft 16 Lapis Lazuli Blocks",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftLapisBlocksChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				],
			],
			self::UPGRADE_MOB_SPAWNER_2 => [
				"name" => "Mob Boi",
				"description" => "Upgrade a mob spawner to level 5",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "UpgradeMobSpawner2Challenge",
				"progress" => [],
			],
			self::KILL_SPIDERS_1 => [
				"name" => "Creepy Crawly",
				"description" => "Kill 25 Spiders",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillSpiders1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_SKELETONS_1 => [
				"name" => "Boney",
				"description" => "Kill 25 Skeletons",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillSkeletons1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_ZOMBIES_1 => [
				"name" => "Apocalypse",
				"description" => "Kill 25 Zombies",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillZombies1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_CREEPERS_1 => [
				"name" => "Scary Crayons",
				"description" => "Kill 25 Creepers",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillCreepers1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::GAIN_PET_XP_1 => [
				"name" => "It's Growing!",
				"description" => "Gain 250 Pet XP",
				"techits" => 2500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "GainPetXp1Challenge",
				"progress" => [
					"gained" => ["progress" => 0, "needed" => 250],
				]
			]
			
		],
		self::LEVEL_7 => [
			self::COLLECT_ACACIA_2 => [
				"name" => "Acacia 200",
				"description" => "Collect 200 Acacia Logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectAcacia2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 200],
				]
			],
			self::BUY_QUARTZ_BLOCK => [
				"name" => "Clout Gang",
				"description" => "Buy 64 Quartz Blocks",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyQuartzBlockChallenge",
				"progress" => [
					"bought" => ["progress" => 0, "needed" => 64],
				]
			],
			self::BUY_NETHERBRICK_BLOCK => [
				"name" => "Nether",
				"description" => "Buy 32 Nether Brick Blocks",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyNetherBrickBlockChallenge",
				"progress" => [
					"bought" => ["progress" => 0, "needed" => 32],
				]
			],
			self::PLANT_ACACIA_SAPLINGS => [
				"name" => "Acacia Farm",
				"description" => "Plant 5 Acacia Saplings",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantAcaciaSaplingsChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5],
				],
			],
			self::COLLECT_DARK_OAK_1 => [
				"name" => "Dark Oak 50",
				"description" => "Collect 50 Dark Oak Logs",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectDarkOak1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::COLLECT_LEAVES => [
				"name" => "Shear It",
				"description" => "Collect 64 Leaves using Shears",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectLeavesChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 64],
				],
			],
			self::LAVA_FISHING_1 => [
				"name" => "Lava Fish?",
				"description" => "Fish in lava 50 times",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "LavaFishing1Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 50],
				]
			],
			self::PLANT_PUMPKINS => [
				"name" => "Halloween!",
				"description" => "Plant 5 pumpkin seeds",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantPumpkinsChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5],
				]
			],
			self::COLLECT_PUMPKINS => [
				"name" => "Pumpkin Man",
				"description" => "Collect 5 Pumpkins",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectPumpkinsChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 5],
				]
			],
			self::BUY_COPPER_GEN => [
				"name" => "Why Is It Green?",
				"description" => "Purchase a Copper Ore Generator",
				"techits" => 750,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyCopperGeneratorChallenge",
				"progress" => []
			],
			self::UPGRADE_COPPER_GEN_1 =>[
				"name" => "Infinite Pennies",
				"description" => "Upgrade a Copper Ore Generator twice!",
				"techits" => 750,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeCopperGenerator1Challenge",
				"progress" => [
					"upgrades" => ["progress" => 0, "needed" => 2]
				]
			],
			self::UPGRADE_MOB_SPAWNER_3 => [
				"name" => "Getting Stronger",
				"description" => "Upgrade a mob spawner to level 10",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeMobSpawner3Challenge",
				"progress" => []
			],
			self::KILL_SPIDERS_2 => [
				"name" => "Get Webbed!",
				"description" => "Kill 100 Spiders",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillSpiders2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_SKELETONS_2 => [
				"name" => "Boney",
				"description" => "Kill 100 Skeletons",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillSkeletons2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_ZOMBIES_2 => [
				"name" => "Apocalypse",
				"description" => "Kill 100 Zombies",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillZombies2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_CREEPERS_2 => [
				"name" => "Creeper Crusher",
				"description" => "Kill 100 Creepers",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillCreepers2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::PET_LEVEL_UP_1 => [
				"name" => "New Tricks!",
				"description" => "Get a pet to level 2 or higher",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "PetLevelUp1Challenge",
				"progress" => []
			]
		],
		self::LEVEL_8 => [
			self::COLLECT_DARK_OAK_2 => [
				"name" => "Dark Oak 200",
				"description" => "Collect 200 Dark Oak Logs",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectDarkOak2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 200],
				]
			],
			self::CRAFT_SNOW_BLOCKS => [
				"name" => "Snow",
				"description" => "Craft 16 snow blocks",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftSnowBlocksChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				]
			],
			self::BUY_BOOKSHELVES => [
				"name" => "Learning Time",
				"description" => "Purchase 16 bookshelves",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyBookshelvesChallenge",
				"progress" => [
					"bought" => ["progress" => 0, "needed" => 16],
				]
			],
			self::PLANT_MELON => [
				"name" => "Watermalone!",
				"description" => "Plant 15 watermelon seeds",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantMelonChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 15],
				],
			],
			self::SELL_MELON => [
				"name" => "Melon Man",
				"description" => "Sell 50 melons",
				"techits" => 350,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellMelonChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 50],
				],
			],
			self::LAVA_FISHING_2 => [
				"name" => "Fire Proof",
				"description" => "Fish in lava 100 times",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "LavaFishing2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 100],
				]
			],
			self::BUY_GOLD_GEN => [
				"name" => "Gold Digger",
				"description" => "Purchase a Gold Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyGoldGeneratorChallenge",
				"progress" => []
			],
			self::SELL_GOLD_BLOCKS => [
				"name" => "Gold",
				"description" => "Sell 16 Gold Blocks",
				"techits" => 350,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellGoldBlocksChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 16],
				],
			],
			self::CRAFT_GOLD_NUGGETS => [
				"name" => "Gold Nugs",
				"description" => "Craft 18 Gold Nuggets",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftGoldNuggetsChallenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 18],
				]
			],
			self::GAIN_PET_XP_2 => [
				"name" => "Still Growing",
				"description" => "Gain 1500 Pet XP",
				"techits" => 3500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "GainPetXp2Challenge",
				"progress" => [
					"gained" => ["progress" => 0, "needed" => 1500],
				]
			],
			self::COLLECT_FISH_4 => [
				"name" => "Just Fishin' Man",
				"description" => "Collect 20 of any kind of fish from Fishing",
				"techits" => 350,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectFish4Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 20],
				],
			]
		],
		self::LEVEL_9 => [
			self::PLANT_DARK_OAK_SAPLINGS => [
				"name" => "Dark Oak Farm",
				"description" => "Plant 5 Dark Oak Saplings",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantDarkOakSaplingsChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 5],
				],
			],
			self::PLANT_NETHER_WART => [
				"name" => "EWWW Warts!",
				"description" => "Plant 15 Nether Wart",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantNetherWartChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 15],
				],
			],
			self::SELL_NETHER_WART => [
				"name" => "Wart Are They For?",
				"description" => "Sell 50 Nether Wart",
				"techits" => 350,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellNetherWartChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 50],
				],
			],
			self::KILL_MOOSHROOM_1 => [
				"name" => "No!! Not Mooshy!",
				"description" => "Kill 25 Mooshrooms",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillMooshroom1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_BLAZES_1 => [
				"name" => "Blaze Powder",
				"description" => "Kill 25 Blazes",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillBlaze1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_BREEZES_1 => [
				"name" => "That Was A Breeze",
				"description" => "Kill 25 Breezes",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillBreeze1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::KILL_ENDERMEN_1 => [
				"name" => "Watch Your Back",
				"description" => "Kill 25 Endermen",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillEndermen1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25],
				]
			],
			self::APPLY_SOLIDIFIER => [
				"name" => "Sounds Hollow?",
				"description" => "Apply a solidifier to a generator",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "ApplySolidifierChallenge",
				"progress" => []
			],
			self::APPLY_EXTENDER => [
				"name" => "It's Expanding!",
				"description" => "Apply an extender to a generator",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "ApplyExtenderChallenge",
				"progress" => []
			],
			self::LAVA_FISHING_3 => [
				"name" => "Is This Safe?",
				"description" => "Fish in lava 150 times",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "LavaFishing3Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 150],
				]
			],
			self::PET_LEVEL_UP_2 => [
				"name" => "What Can It Do?",
				"description" => "Get a pet to level 3 or higher",
				"techits" => 2500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "PetLevelUp2Challenge",
				"progress" => []
			],
			self::REFINE_ESSENCE_2 => [
				"name" => "Essence of Refinement!",
				"description" => "Refine 25 essence of any type.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 2500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "RefineEssence2Challenge",
				"progress" => [
					"refined" => ["progress" => 0, "needed" => 25],
				]
			]
		],
		self::LEVEL_10 => [
			self::SELL_DIAMONDS => [
				"name" => "Shine Bright",
				"description" => "Sell 16 Diamonds",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellDiamondsChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 16],
				],
			],
			self::BUY_END_STONE => [
				"name" => "The End",
				"description" => "Purchase 32 End Stone",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyEndStoneChallenge",
				"progress" => [
					"bought" => ["progress" => 0, "needed" => 32],
				],
			],
			self::BUY_AUTOMINER => [
				"name" => "Autominer",
				"description" => "Purchase an Autominer",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyAutominerChallenge",
				"progress" => [],
			],
			self::PLANT_BAMBOO => [
				"name" => "Panda Express",
				"description" => "Plant 15 bamboo",
				"techits" => 250,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantBambooChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 15]
				]
			],
			self::SELL_BAMBOO => [
				"name" => "Bam Booty",
				"description" => "Sell 150 bamboo",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellBambooChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 150]
				]
			],
			self::BUY_DIAMOND_GEN => [
				"name" => "Infinite Diamonds?!",
				"description" => "Purchase a Diamond Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyDiamondGeneratorChallenge",
				"progress" => []
			],
			self::CRAFT_DIAMOND_BLOCKS_1 => [
				"name" => "57",
				"description" => "Craft 16 Diamond Blocks",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CraftDiamondBlocks1Challenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				],
			],
			self::APPLY_ENCHANTMENT_3 => [
				"name" => "You're Enchanting!",
				"description" => "Apply 25 enchantments to any item.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 7500,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "ApplyEnchantment3Challenge",
				"progress" => [
					"applied" => ["progress" => 0, "needed" => 25],
				],
			],
			self::UPGRADE_DIAMOND_GEN_1 => [
				"name" => "Gimme dat loot BOIIIII",
				"description" => "Upgrade a diamond ore generator to level 5",
				"techits" => 3000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "UpgradeDiamondGenerator1Challenge",
				"progress" => []
			],
		],
		self::LEVEL_11 => [
			self::BUY_WHITE_STAINED_GLASS => [
				"name" => "Stained Mirrors",
				"description" => "Buy 16 white stained glass",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyWhiteStainedGlassChallenge",
				"progress" => [
					"bought" => ["progress" => 0, "needed" => 16],
				]
			],
			self::COLLECT_ROTTEN_FLESH => [
				"name" => "Fresh Meat",
				"description" => "Collect 20 Rotten Flesh",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectRottenFleshChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 20],
				],
			],
			self::LEVEL_UP => [
				"name" => "X Pee?!?",
				"description" => "Reach 20 levels of experience",
				"techits" => 100,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "LevelUpChallenge",
				"progress" => [
					"level" => ["progress" => 0, "needed" => 20],
				],
			],
			self::COLLECT_EMERALD => [
				"name" => "Emeralds",
				"description" => "Collect 16 emeralds",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectEmeraldChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 16],
				]
			],
			self::REPAIR_ITEM => [
				"name" => "Waste of XP",
				"description" => "Use your XP Levels to repair an item",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "RepairItemChallenge",
				"progress" => []
			],
			self::COLLECT_PRISMARINE_SHARDS => [
				"name" => "Light of the Sea",
				"description" => "Collect 32 Prismarine Shards",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectPrismarineShardsChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 32]
				],
			],
			self::PLANT_RED_MUSHROOM => [
				"name" => "Red Room",
				"description" => "Plant 50 red mushrooms",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantRedMushroomChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 50]
				],
			],
			self::SELL_RED_MUSHROOM => [
				"name" => "Shroomz",
				"description" => "Sell 500 red mushrooms",
				"techits" => 150,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellRedMushroomChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 500]
				],
			],
			self::BUY_EMERALD_GEN => [
				"name" => "A Shiny Green Upgrade",
				"description" => "Purchase an Emerald Ore Generator",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyEmeraldGeneratorChallenge",
				"progress" => []
			],
			self::UPGRADE_EMERALD_GEN_1 => [
				"name" => "So Much Green",
				"description" => "Upgrade an emerald ore generator to level 5",
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeEmeraldGenerator1Challenge",
				"progress" => []
			],
			self::CRAFT_EMERALD_BLOCKS_1 => [
				"name" => "Jade",
				"description" => "Craft 16 emerald blocks",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftEmeraldBlocks1Challenge",
				"progress" => [
					"crafted" => ["progress" => 0, "needed" => 16],
				]
			],
			self::KILL_MOOSHROOM_2 => [
				"name" => "Moov Out The Way",
				"description" => "Kill 100 Mooshrooms",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillMooshroom2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_BLAZES_2 => [
				"name" => "Blaze it",
				"description" => "Kill 100 Blazes",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillBlaze2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_BREEZES_2 => [
				"name" => "Chris Breezy",
				"description" => "Kill 100 Breezes",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillBreeze2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 100],
				]
			],
			self::KILL_ENDERMEN_2 => [
				"name" => "DIE DIE DIE",
				"description" => "Kill 200 Endermen",
				"techits" => 750,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "KillEndermen2Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 200],
				]
			],
		],
		self::LEVEL_12 => [
			self::BUY_PURPUR_BLOCKS => [
				"name" => "PurrPurple",
				"description" => "Buy 20 purpur blocks",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyPurpurBlockChallenge",
				"progress" => [
					"bought" => ["progress" => 0, "needed" => 20],
				]
			],
			self::PLANT_BROWN_MUSHROOM => [
				"name" => "Can I Eat It?",
				"description" => "Plant 50 Brown Mushrooms",
				"techits" => 350,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantBrownMushroomChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 50]
				]
			],
			self::SELL_BROWN_MUSHROOM => [
				"name" => "What's Brown and Mushy?",
				"description" => "Sell 500 Brown Mushrooms",
				"techits" => 350,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "SellBrownMushroomChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 500]
				]
			],
			self::BUY_OBSIDIAN_GEN => [
				"name" => "Jelly",
				"description" => "Purchase an Obsidian Generator",
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyObsidianGeneratorChallenge",
				"progress" => []
			],
			self::UPGRADE_OBSIDIAN_GEN_1 => [
				"name" => "Obby Boi",
				"description" => "Upgrade an obsidian generator to level 5",
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "UpgradeObsidianGenerator1Challenge",
				"progress" => []
			],
			self::MINE_OBSIDIAN_1 => [
				"name" => "10 Minutes of Mining",
				"description" => "Mine 16 Obsidian",
				"techits" => 350,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BreakObsidian1Challenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 16],
				],
			],
			self::COLLECT_FISH_5 => [
				"name" => "Fisherdinging",
				"description" => "Collect 25 of any kind of fish from Fishing",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectFish5Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 25],
				],
			]
		],
		self::LEVEL_13 => [
			self::CRAFT_CLOCK => [
				"name" => "Out of Time",
				"description" => "Craft a clock",
				"techits" => 300,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CraftClockChallenge",
				"progress" => []
			],
			self::BUY_PURPUR_QUARTZ_STONE_BRICK => [
				"name" => "Aesthetics Design",
				"description" => "Purchase 20 Purpur blocks, 20 quartz blocks, and 20 stone bricks",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyPurpurQuartzStoneBrickChallenge",
				"progress" => [
					"purpur" => ["progress" => 0, "needed" => 20],
					"quartz" => ["progress" => 0, "needed" => 20],
					"stone" => ["progress" => 0, "needed" => 20],
				]
			],
			self::BUY_BLACK_WOOL_CONCRETE => [
				"name" => "Black Hole",
				"description" => "Purchase 16 black wool and 16 black concrete",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyBlackWoolConcreteChallenge",
				"progress" => [
					"wool" => ["progress" => 0, "needed" => 16],
					"concrete" => ["progress" => 0, "needed" => 16],
				]
			],
			self::UPGRADE_DIAMOND_GEN_2 => [
				"name" => "Da Dimond Farmer",
				"description" => "Upgrade a diamond ore generator to level 8",
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeDiamondGenerator2Challenge",
				"progress" => []
			],
			self::PLANT_CHORUS_FRUIT => [
				"name" => "Choir",
				"description" => "Plant 15 Chorus Flowers",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "PlantChorusFruitChallenge",
				"progress" => [
					"planted" => ["progress" => 0, "needed" => 15]
				]
			],
			self::SELL_CHORUS_FRUIT => [
				"name" => "Health Hazard!",
				"description" => "Sell 75 Chorus Fruit",
				"techits" => 450,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "SellChorusFruitChallenge",
				"progress" => [
					"sold" => ["progress" => 0, "needed" => 75]
				]
			],
			self::BUY_GLOWING_OBSIDIAN_GEN => [
				"name" => "Globby!!",
				"description" => "Purchase a Glowing Obsidian generator",
				"techits" => 450,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyGlowingObsidianGeneratorChallenge",
				"progress" => []
			],
			self::UPGRADE_GLOWING_OBSIDIAN_GEN_1 => [
				"name" => "Wow it glows!",
				"description" => "Upgrade a glowing obsidian generator to level 5",
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeGlowingObsidianGenerator1Challenge",
				"progress" => []
			],
			self::REFINE_ESSENCE_3 => [
				"name" => "An Essencetial Process!",
				"description" => "Refine 75 essence of any type.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "RefineEssence3Challenge",
				"progress" => [
					"refined" => ["progress" => 0, "needed" => 75],
				]
			],
			self::COLLECT_IRON_KEYS_2 => [
				"name" => "Iron Lad",
				"description" => "Collect 80 iron keys",
				"techits" => 2500,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "CollectIronKeys2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 80]
				],
			]
		],
		self::LEVEL_14 => [
			self::APPLY_ENCHANTMENT_4 => [
				"name" => "Not Shiny Enough!",
				"description" => "Apply 50 enchantments to items.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 10000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "ApplyEnchantment4Challenge",
				"progress" => [
					"applied" => ["progress" => 0, "needed" => 50],
				],
			],
			self::BUY_MAGMA => [
				"name" => "The floor is magma",
				"description" => "Buy a magma block",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyMagmaChallenge",
				"progress" => []
			],
			self::COLLECT_GOLD_KEYS_2 => [
				"name" => "24 Karat Keys",
				"description" => "Collect 100 gold keys",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectGoldKeys2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 100]
				]
			],
			self::COLLECT_WITHER_SKULL => [
				"name" => "Withering Mask",
				"description" => "Obtain 5 wither skeleton heads",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "CollectWitherSkullChallenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 5]
				]
			],
			self::KILL_WITCH_1 => [
				"name" => "Sandwitch!",
				"description" => "Kill 25 witches",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillWitch1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25]
				]
			],
			self::KILL_GOLEM_1 => [
				"name" => "Roses are Red",
				"description" => "Kill 25 Iron Golems",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "KillGolem1Challenge",
				"progress" => [
					"killed" => ["progress" => 0, "needed" => 25]
				]
			],
			self::MINE_ANCIENT_DEBRIS_1 => [
				"name" => "Duh Bree",
				"description" => "Mine 15 blocks of Ancient Debris",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "MineAncientDebris1Challenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 15]
				]
			],
			self::UPGRADE_MOB_SPAWNER_4 => [
				"name" => "I'm Wit Da Mob",
				"description" => "Upgrade a mob spawner to level 15",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeMobSpawner4Challenge",
				"progress" => [],
			],
		],
		self::LEVEL_15 => [
			self::BUY_ARMOR_STAND => [
				"name" => "Lookin' Fancy",
				"description" => "Purchase an Armor Stand",
				"techits" => 200,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "BuyArmorStandChallenge",
				"progress" => []
			],
			self::BUY_DIMENSIONAL => [
				"name" => "A portal to a new dimension?!",
				"description" => "Buy a dimensional block",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "BuyDimensionalChallenge",
				"progress" => []
			],
			self::BUY_ELYTRA => [
				"name" => "I believe I can fly",
				"description" => "Purchase an elytra",
				"techits" => 500,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "BuyElytraChallenge",
				"progress" => []
			],
			self::COLLECT_DIAMOND_KEYS_2 => [
				"name" => "Ooo Shiny Key!",
				"description" => "Collect 100 diamond keys",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "CollectDiamondKeys2Challenge",
				"progress" => [
					"collected" => ["progress" => 0, "needed" => 80]
				]
			],
			self::JUMP_1 => [
				"name" => "Grasshopper",
				"description" => "Jump 1,000 times",
				"techits" => 100000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "Jump1Challenge",
				"progress" => [
					"jumps" => ["progress" => 0, "needed" => 1000],
				]
			],
			self::MINE_ANCIENT_DEBRIS_2 => [
				"name" => "Double Buhbree",
				"description" => "Mine 50 blocks of Ancient Debris",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "MineAncientDebris2Challenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 50]
				]
			],
			self::MINE_GILDED_OBSIDIAN_1 => [
				"name" => "It's Got Gold!",
				"description" => "Mine 15 blocks of Gilded Obsidian",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				"class" => "MineGildedObsidian1Challenge",
				"progress" => [
					"blocks" => ["progress" => 0, "needed" => 15]
				]
			],
			self::UPGRADE_EMERALD_ORE_GEN_2 => [
				"name" => "Villagers Love Me",
				"description" => "Upgrade an emerald ore generator to level 8",
				"techits" => 10000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "UpgradeEmeraldGenerator2Challenge",
				"progress" => []
			],
		],
		self::LEVEL_16 => [
			self::SNEAK_1 => [
				"name" => "@sn3akr_",
				"description" => "Sneak 5,000 times",
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "",
				"progress" => [
					"sneaked" => ["progress" => 0, "needed" => 2500],
				]
			],
			self::UPGRADE_OBSIDIAN_GEN_2 => [
				"name" => "You are now poor",
				"description" => "Upgrade an Obsidian Generator to level 8",
				"techits" => 10000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => []
			],
			self::UPGRADE_GLOWING_OBSIDIAN_GEN_2 => [
				"name" => "So bright omg",
				"description" => "Upgrade a Glowing Obsidian Generator to level 8",
				"techits" => 10000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => []
			],
			self::COLLECT_EMERALD_KEYS_2 => [
				"name" => "Head Villager",
				"description" => "Collect 100 emerald keys",
				"techits" => 10000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => [
					"keys" => ["progress" => 0, "needed" => 100]
				]
			]
		],
		self::LEVEL_17 => [
			self::UPGRADE_ANCIENT_DEBRIS_1 => [
				"name" => "It's Ancient",
				"description" => "Upgrade an Ancient Debris Generator to level 5",
				"techits" => 10000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => []
			],
			self::KILL_WITCH_2 => [
				"name" => "Witch Way Now??",
				"description" => "Kill 500 witches",
				"techits" => 50000,
				"difficulty" => self::DIFFICULTY_HARD,
				// "class" => "",
				"progress" => [
					"witches" => ["progress" => 0, "needed" => 500]
				]
			],
			self::REFINE_ESSENCE_4 => [
				"name" => "A Refined Experience",
				"description" => "Refine 75 essence of any type.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_HARD,
				// "class" => "KillPigs1Challenge",
				"progress" => [
					"refined" => ["progress" => 0, "needed" => 75],
				]
			],
			self::UPGRADE_MOB_SPAWNER_5 => [
				"name" => "Final Battle",
				"description" => "Max out your mob spawner to level 17",
				"techits" => 1000,
				"difficulty" => self::DIFFICULTY_HARD,
				// "class" => "UpgradeMobSpawner1Challenge",
				"progress" => [],
			],
		],
		self::LEVEL_18 => [
			self::UPGRADE_GILDED_OBSIDIAN_1 => [
				"name" => "I Like This Block!",
				"description" => "Upgrade a gilded obsidian generator to level 5",
				"techits" => 10000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "UpgradeObsidianGen2Challenge",
				"progress" => []
			],
			self::KILL_GOLEM_2 => [
				"name" => "Rose Garden",
				"description" => "Kill 500 Iron Golems",
				"techits" => 50000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => [
					"witches" => ["progress" => 0, "needed" => 500]
				]
			],
			self::COLLECT_FISH_6 => [
				"name" => "Master Baiter",
				"description" => "Collect 500 of any kind of fish from Fishing",
				"techits" => 10000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "CollectFishChallenge",
				"progress" => [
					"fish" => ["progress" => 0, "needed" => 500],
				],
			],
			self::EXHAUST_PET_1 => [
				"name" => "Fetch!",
				"description" => "Make your pet run out of energy",
				"techits" => 50000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => []
			]
		],
		self::LEVEL_19 => [
			self::UPGRADE_ANCIENT_DEBRIS_2 => [
				"name" => "Netherite",
				"description" => "Upgrade an Ancient Debris Generator to level 8",
				"techits" => 50000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => []
			],
			self::JUMP_2 => [
				"name" => "Kangarooooo",
				"description" => "Jump 5,000 times",
				"techits" => 1000000,
				"difficulty" => self::DIFFICULTY_HARD,
				"class" => "Jump2Challenge",
				"progress" => [
					"jumps" => ["progress" => 0, "needed" => 5000],
				]
			],
			self::APPLY_ENCHANTMENT_5 => [
				"name" => "Divine!",
				"description" => "Apply 100 enchantments to items.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 500000,
				"difficulty" => self::DIFFICULTY_HARD,
				// "class" => "CollectCactusChallenge",
				"progress" => [
					"applied" => ["progress" => 0, "needed" => 100],
				],
			]
		],
		self::LEVEL_20 => [
			self::UPGRADE_GILDED_OBSIDIAN_2 => [
				"name" => "Is It Heavy?",
				"description" => "Upgrade a Gilded Obsidian Generator to level 8",
				"techits" => 50000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => []
			],
			self::SNEAK_2 => [
				"name" => "Sneak like Sn3ak",
				"description" => "Sneak 10,000 times",
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "",
				"progress" => [
					"sneaked" => ["progress" => 0, "needed" => 10000],
				]
			],
			self::PET_MODE_SWITCH => [
				"name" => "What Does This Mean?",
				"description" => "Switch your pets mode 1,500 times",
				"techits" => 5000,
				"difficulty" => self::DIFFICULTY_EASY,
				"class" => "",
				"progress" => [
					"sneaked" => ["progress" => 0, "needed" => 1500],
				]
			],
			self::REFINE_ESSENCE_5 => [
				"name" => "Refine Dining",
				"description" => "Refine 100 essence of any type.\n\n" . TF::YELLOW . "ONLY WORKS AT ISLAND" . TF::RESET,
				"techits" => 350000,
				"difficulty" => self::DIFFICULTY_HARD,
				// "class" => "KillPigs1Challenge",
				"progress" => [
					"refined" => ["progress" => 0, "needed" => 100],
				]
			],
			self::EXHAUST_PET_2 => [
				"name" => "I'm Tired",
				"description" => "Make your pet run out of energy twice",
				"techits" => 50000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => [
					"exhaust" => ["progress" => 0, "needed" => 2]
				]
			],
			self::PET_MAX_LEVEL => [
				"name" => "It's Evolving!",
				"description" => "Level up your pet to level 50",
				"techits" => 100000,
				"difficulty" => self::DIFFICULTY_NORMAL,
				// "class" => "",
				"progress" => []
			],
		],
	];

	//1 - 100
	const ISLAND_EXPAND = 100;
	const FURNACE_CRAFT = 101;
	const BED_CRAFT = 102;
	const PLANT_SUGARCANE = 103;
	const PLANT_CACTUS = 104;
	const TRAPDOOR_CRAFT = 105;
	const COLLECT_FISH_1 = 106;
	const MINE_COBBLESTONE_1 = 107;
	const BREAK_WOOD_1 = 108;
	const COLLECT_IRON_KEYS_1 = 109; // new

	//2 - 200
	const BREAK_WOOD_2 = 200;
	const MINE_COBBLESTONE_2 = 201;
	const COBBLESTONE_OAK_STAIR = 202;
	const COBBLESTONE_SLAB_CRAFT = 203;
	const BUTTON_CRAFT = 204;
	const CRAFT_BRICKS = 205;
	const CRAFT_PANES = 206;
	const COLLECT_SUGARCANE = 207;
	const COLLECT_CACTUS = 208;
	const BUY_COAL_GEN = 209;
	const UPGRADE_COAL_GEN_1 = 210;
	const CRAFT_COAL_BLOCKS = 211;
	const APPLY_ENCHANTMENT_1 = 212;
	const COLLECT_GOLD_KEYS_1 = 213;

	//3 - 300
	const CRAFT_CHEST = 300;
	const PLANT_WHEAT = 301;
	const COLLECT_WHEAT = 302;
	const CRAFT_FENCE = 303;
	const CRAFT_GATE = 304;
	const PLANT_OAK_SAPLING = 305;
	const CRAFT_TORCH = 306;
	const CRAFT_SIGN = 307;
	const CRAFT_GREEN_WOOL = 308;
	const COLLECT_FISH_2 = 309;
	const BUY_IRON_GEN = 310;
	const UPGRADE_IRON_GEN_1 = 311;
	const CRAFT_IRON_BLOCKS = 312;
	const CRAFT_IRON_NUGGETS = 313;
	const COLLECT_DIAMOND_KEYS_1 = 314;

	//4 - 400
	const COLLECT_SPRUCE_1 = 400;
	const CRAFT_BREAD = 401;
	const CRAFT_STONE_BRICKS = 402;
	const CRAFT_WHITE_WOOL = 404;
	const CRAFT_PAINTINGS = 405;
	const CRAFT_LADDERS = 406;
	const CRAFT_BOW = 407;
	const BONEMEAL_SAPLINGS = 408;
	const COLLECT_POTATOES = 409;
	const UPGRADE_MOB_SPAWNER_1 = 410;
	const KILL_PIGS_1 = 411;
	const KILL_CHICKENS_1 = 412;
	const KILL_SHEEP_1 = 413;
	const KILL_COWS_1 = 414;
	const REFINE_ESSENCE_1 = 415;
	const COLLECT_EMERALD_KEYS_1 = 416;

	//5 - 500
	const COLLECT_SPRUCE_2 = 500;
	const CRAFT_POLISHED_GRANITE = 501;
	const KILL_PIGS_2 = 502;
	const KILL_CHICKENS_2 = 503;
	const KILL_SHEEP_2 = 504;
	const KILL_COWS_2 = 505;
	const GROW_BIRCH_SAPLINGS = 506;
	const COLLECT_COAL = 507;
	const COLLECT_JUNGLE_1 = 508;
	const SELL_CARROTS = 509;
	const COLLECT_FISH_3 = 510;
	const BUY_REDSTONE_GEN = 511;
	const UPGRADE_REDSTONE_GEN_1 = 512;
	const CRAFT_REDSTONE_BLOCKS = 513;
	const UNLOCK_PET_BOX = 514;
	const APPLY_ENCHANTMENT_2 = 515;

	//6 - 600
	const COLLECT_JUNGLE_2 = 600;
	const COLLECT_ACACIA_1 = 601;
	const CRAFT_POLISHED_DIORITE = 602;
	const PLANT_JUNGLE_SAPLINGS = 603;
	const PLACE_VINES = 604;
	const CRAFT_CARPET = 605;
	const PLANT_BEETROOT = 606;
	const CRAFT_BEETROOT_SOUP = 607;
	const BUY_LAPIS_GEN = 608;
	const UPGRADE_LAPIS_GEN = 609;
	const CRAFT_LAPIS_BLOCKS = 610;
	const UPGRADE_MOB_SPAWNER_2 = 611;
	const KILL_SPIDERS_1 = 612;
	const KILL_SKELETONS_1 = 613;
	const KILL_ZOMBIES_1 = 614;
	const KILL_CREEPERS_1 = 615;
	const GAIN_PET_XP_1 = 616;

	//7 - 700
	const COLLECT_ACACIA_2 = 700;
	const BUY_QUARTZ_BLOCK = 701;
	const BUY_NETHERBRICK_BLOCK = 702;
	const PLANT_ACACIA_SAPLINGS = 703;
	const COLLECT_DARK_OAK_1 = 704;
	const COLLECT_LEAVES = 705;
	const LAVA_FISHING_1 = 706;
	const PLANT_PUMPKINS = 707;
	const COLLECT_PUMPKINS = 708;
	const BUY_COPPER_GEN = 709;
	const UPGRADE_COPPER_GEN_1 = 710;
	const SELL_COPPER_BLOCKS = 711;
	const UPGRADE_MOB_SPAWNER_3 = 712;
	const KILL_SPIDERS_2 = 713;
	const KILL_SKELETONS_2 = 714;
	const KILL_ZOMBIES_2 = 715;
	const KILL_CREEPERS_2 = 716;
	const PET_LEVEL_UP_1 = 717;

	//8 - 800
	const COLLECT_DARK_OAK_2 = 800;
	const CRAFT_SNOW_BLOCKS = 801;
	const BUY_BOOKSHELVES = 802;
	const PLANT_MELON = 803;
	const SELL_MELON = 804;
	const LAVA_FISHING_2 = 805;
	const BUY_GOLD_GEN = 806;
	const SELL_GOLD_BLOCKS = 807;
	const CRAFT_GOLD_NUGGETS = 808;
	const GAIN_PET_XP_2 = 809;
	const COLLECT_FISH_4 = 810;

	//9 - 900
	const PLANT_DARK_OAK_SAPLINGS = 900;
	const PLANT_NETHER_WART = 901;
	const SELL_NETHER_WART = 902;
	const KILL_MOOSHROOM_1 = 903;
	const KILL_BLAZES_1 = 904;
	const KILL_BREEZES_1 = 905;
	const KILL_ENDERMEN_1 = 906;
	const APPLY_SOLIDIFIER = 907;
	const APPLY_EXTENDER = 908;
	const LAVA_FISHING_3 = 909;
	const PET_LEVEL_UP_2 = 910;
	const REFINE_ESSENCE_2 = 911;

	//10 - 1000
	const SELL_DIAMONDS = 1000;
	const BUY_END_STONE = 1001;
	const BUY_AUTOMINER = 1002;
	const PLANT_BAMBOO = 1003;
	const SELL_BAMBOO = 1004;
	const BUY_DIAMOND_GEN = 1005;
	const CRAFT_DIAMOND_BLOCKS_1 = 1006;
	const APPLY_ENCHANTMENT_3 = 1007;
	const UPGRADE_DIAMOND_GEN_1 = 1008;

	//11 - 1100
	const BUY_WHITE_STAINED_GLASS = 1100;
	const COLLECT_ROTTEN_FLESH = 1101;
	const LEVEL_UP = 1102;
	const COLLECT_EMERALD = 1103;
	const REPAIR_ITEM = 1104;
	const COLLECT_PRISMARINE_SHARDS = 1105;
	const PLANT_RED_MUSHROOM = 1107;
	const SELL_RED_MUSHROOM = 1108;
	const BUY_EMERALD_GEN = 1109;
	const UPGRADE_EMERALD_GEN_1 = 1110;
	const CRAFT_EMERALD_BLOCKS_1 = 1111;
	const KILL_MOOSHROOM_2 = 1112;
	const KILL_BLAZES_2 = 1113;
	const KILL_BREEZES_2 = 1114;
	const KILL_ENDERMEN_2 = 1115;

	//12 - 1200
	const BUY_PURPUR_BLOCKS = 1200;
	const PLANT_BROWN_MUSHROOM = 1201;
	const SELL_BROWN_MUSHROOM = 1202;
	const BUY_OBSIDIAN_GEN = 1203;
	const UPGRADE_OBSIDIAN_GEN_1 = 1204;
	const MINE_OBSIDIAN_1 = 1205;
	const COLLECT_FISH_5 = 1206;

	//13 - 1300
	const CRAFT_CLOCK = 1300;
	const BUY_PURPUR_QUARTZ_STONE_BRICK = 1301;
	const BUY_BLACK_WOOL_CONCRETE = 1302;
	const UPGRADE_DIAMOND_GEN_2 = 1303;
	const PLANT_CHORUS_FRUIT = 1304;
	const SELL_CHORUS_FRUIT = 1305;
	const BUY_GLOWING_OBSIDIAN_GEN = 1306;
	const UPGRADE_GLOWING_OBSIDIAN_GEN_1 = 1307;
	const MINE_GLOWING_OBSIDIAN_1 = 1308;
	const REFINE_ESSENCE_3 = 1309;
	const COLLECT_IRON_KEYS_2 = 1310;

	//14 - 1400
	const BUY_MAGMA = 1400;
	const MINE_ANCIENT_DEBRIS_1 = 1401;
	const COLLECT_WITHER_SKULL = 1402;
	const UPGRADE_MOB_SPAWNER_4 = 1403;
	const KILL_WITCH_1 = 1404;
	const KILL_GOLEM_1 = 1405;
	const APPLY_ENCHANTMENT_4 = 1406;
	const COLLECT_GOLD_KEYS_2 = 1407;

	//15 - 1500
	const BUY_DIMENSIONAL = 1500;
	const BUY_ELYTRA = 1501;
	const BUY_ARMOR_STAND = 1502;
	const JUMP_1 = 1503;
	const UPGRADE_EMERALD_ORE_GEN_2 = 1504;
	const MINE_GILDED_OBSIDIAN_1 = 1505;
	const MINE_ANCIENT_DEBRIS_2 = 1506;
	const COLLECT_DIAMOND_KEYS_2 = 1507;
	
	//16 - 1600
	const SNEAK_1 = 1600;
	const UPGRADE_OBSIDIAN_GEN_2 = 1601;
	const UPGRADE_GLOWING_OBSIDIAN_GEN_2 = 1602;
	const COLLECT_EMERALD_KEYS_2 = 1603;

	//17 - 1700
	const UPGRADE_ANCIENT_DEBRIS_1 = 1700;
	const KILL_WITCH_2 = 1701;
	const REFINE_ESSENCE_4 = 1702;
	const UPGRADE_MOB_SPAWNER_5 = 1703; // max

	//18 - 1800
	const UPGRADE_GILDED_OBSIDIAN_1 = 1800;
	const KILL_GOLEM_2 = 1801;
	const COLLECT_FISH_6 = 1802;
	const EXHAUST_PET_1 = 1803;

	//19 - 1900
	const UPGRADE_ANCIENT_DEBRIS_2 = 1900;
	const JUMP_2 = 1901;
	const APPLY_ENCHANTMENT_5 = 1902;

	//20 - 2000
	const UPGRADE_GILDED_OBSIDIAN_2 = 2000;
	const SNEAK_2 = 2001;
	const PET_MODE_SWITCH = 2002;
	const REFINE_ESSENCE_5 = 2003;
	const EXHAUST_PET_2 = 2004;
	const PET_MAX_LEVEL = 2005;
}
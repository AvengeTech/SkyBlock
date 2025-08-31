<?php namespace skyblock\crates;

use skyblock\crates\filter\FilterSetting;

class CratePrizes{

	/** Entries go "item" => ["subRarity" => number, "filter" => filter type]
	 * 
	 * Normal Items: i:<id>[:count]
	 * Custom Items:
	 * 		Enchantment Books: i:redeemable_book:<count>:<type>:<rarity>:<include_divine>
	 * 			Types: LEGACY (S1) = 0; RARITY = 1; RARITY = 2; RANDOM_RARITY = 3; MAX_RANDOM_RARITY = 4;
	 * 			Rarities: 0-5 <=> Common-Divine
	 * 			Include Divine: FALSE = 0; TRUE = 1;
	 * 
	 * 		Essence: i:essence_of_<>:<count>:<rarity>[:<cost>:<percent>:<raw>]
	 * 			In the case of non-EoS, percent is ommitted
	 * 			In the case of EoA, cost and percent are ommitted
	 * 
	 * 		Unbound Tome: i:unbound_tome:<count>:<chance>
	 * Effects: e:<id>:<level>:<duration(sec)>
	 * Tag: pvf:tag
	 * Techits: pvf:t:<amount>
	 * Key Packs: pvf:kp:<count>:<size>
	 * Keys: pvf:ck:<count>:<type>
	 */
	const PRIZES = [
		CrateData::RARITY_COMMON => [
			"i:shears" => [1, FilterSetting::FILTER_TOOLS],
			"i:fishing_rod" => [1, FilterSetting::FILTER_TOOLS],

			"i:coal:16" => [0, FilterSetting::FILTER_MISCELLANEOUS],
			"i:charcoal:16" => [0, FilterSetting::FILTER_MISCELLANEOUS],
			"i:iron_ingot:8" => [0, FilterSetting::FILTER_MISCELLANEOUS],

			"i:nametag:1" => [1, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:death_tag:1" => [1, FilterSetting::FILTER_CUSTOM_ITEMS],

			"i:golden_apple:1" => [0, FilterSetting::FILTER_FOOD],

			"i:bottle_o_enchanting:4" => [0, FilterSetting::FILTER_BOOKS],
			"i:bottle_o_enchanting:8" => [1, FilterSetting::FILTER_BOOKS],

			"i:max_book:1:1:1:0" => [5, FilterSetting::FILTER_BOOKS],
			"i:essence_of_success:1:1" => [3, FilterSetting::FILTER_CUSTOM_ITEMS],

			"i:redeemed_book:1:1" => [3, FilterSetting::FILTER_BOOKS],

			"pvf:t:500" => [1, FilterSetting::FILTER_NONE],

			"pvf:ck:2:iron" => [5, FilterSetting::FILTER_NONE],
			"pvf:ck:1:gold" => [5, FilterSetting::FILTER_NONE],
		],
		CrateData::RARITY_UNCOMMON => [
			"i:nametag:1" => [0, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:death_tag:1" => [0, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:stone_pressure_plate:1" => [0, FilterSetting::FILTER_CUSTOM_ITEMS],

			"i:iron_ingot:16" => [0, FilterSetting::FILTER_MISCELLANEOUS],
			"i:gold_ingot:8" => [0, FilterSetting::FILTER_MISCELLANEOUS],

			"i:bottle_o_enchanting:8" => [1, FilterSetting::FILTER_BOOKS],
			"i:bottle_o_enchanting:16" => [2, FilterSetting::FILTER_BOOKS],
			"i:redeemed_book:1:2" => [1, FilterSetting::FILTER_BOOKS],

			"i:max_book:1:1:2:0" => [4, FilterSetting::FILTER_BOOKS],
			"i:essence_of_success:1:2" => [2, FilterSetting::FILTER_CUSTOM_ITEMS],

			"i:golden_apple:8" => [1, FilterSetting::FILTER_FOOD],
			"i:golden_apple:16" => [2, FilterSetting::FILTER_FOOD],

			"pvf:t:1000" => [1, FilterSetting::FILTER_NONE],

			"pvf:ck:1:diamond" => [5, FilterSetting::FILTER_NONE],
			"pvf:ck:2:gold" => [5, FilterSetting::FILTER_NONE],
			"pvf:ck:2:iron" => [5, FilterSetting::FILTER_NONE],
		],
		CrateData::RARITY_RARE => [
			"i:iron_ingot:32" => [0, FilterSetting::FILTER_MISCELLANEOUS],
			"i:gold_ingot:16" => [0, FilterSetting::FILTER_MISCELLANEOUS],
			"i:diamond:8" => [0, FilterSetting::FILTER_MISCELLANEOUS],

			"i:golden_apple:16" => [1, FilterSetting::FILTER_FOOD],
			"i:golden_apple:32" => [2, FilterSetting::FILTER_FOOD],

			"i:bottle_o_enchanting:16" => [1, FilterSetting::FILTER_BOOKS],
			"i:bottle_o_enchanting:32" => [2, FilterSetting::FILTER_BOOKS],
			"i:redeemed_book:1:3" => [1, FilterSetting::FILTER_BOOKS],

			"i:unbound_tome:1" => [3, FilterSetting::FILTER_BOOKS],
			"i:max_book:1:1:3:0" => [3, FilterSetting::FILTER_BOOKS],
			"i:essence_of_success:1:3" => [2, FilterSetting::FILTER_CUSTOM_ITEMS],

			"pvf:t:2000" => [0, FilterSetting::FILTER_NONE],
			"pvf:tag:1" => [0, FilterSetting::FILTER_NONE],
			"pvf:tag:1" => [1, FilterSetting::FILTER_NONE],

			"pvf:ck:1:emerald" => [5, FilterSetting::FILTER_NONE],
			"pvf:ck:2:diamond" => [5, FilterSetting::FILTER_NONE],
			"pvf:ck:2:gold" => [5, FilterSetting::FILTER_NONE],
			"pvf:ck:2:iron" => [5, FilterSetting::FILTER_NONE],
		],
		CrateData::RARITY_LEGENDARY => [
			"i:iron_ingot:64" => [0, FilterSetting::FILTER_MISCELLANEOUS],
			"i:gold_ingot:32" => [0, FilterSetting::FILTER_MISCELLANEOUS],
			"i:diamond:16" => [0, FilterSetting::FILTER_MISCELLANEOUS],
			"i:emerald:8" => [0, FilterSetting::FILTER_MISCELLANEOUS],

			"i:bottle_o_enchanting:16" => [0, FilterSetting::FILTER_BOOKS],
			"i:bottle_o_enchanting:32" => [1, FilterSetting::FILTER_BOOKS],
			"i:unbound_tome:1:100" => [4, FilterSetting::FILTER_BOOKS],

			"i:redeemed_book:1:3" => [0, FilterSetting::FILTER_BOOKS],
			"i:redeemed_book:1:4" => [1, FilterSetting::FILTER_BOOKS],

			"i:enchanted_golden_apple:1" => [2, FilterSetting::FILTER_FOOD],
			"i:enchanted_golden_apple:2" => [3, FilterSetting::FILTER_FOOD],

			"i:gen_booster:2:50" => [1, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:unbound_tome:1" => [2, FilterSetting::FILTER_BOOKS],

			"i:gummy_orb:1:1:5" => [4, FilterSetting::FILTER_PET_ITEMS],
			"i:energy_booster:1:1:0.05" => [4, FilterSetting::FILTER_PET_ITEMS],

			"i:effect_item:1" => [0, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:max_book:1:2:1:0" => [2, FilterSetting::FILTER_BOOKS],

			"i:essence_of_success:1:4" => [2, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:essence_of_knowledge:1:4" => [2, FilterSetting::FILTER_CUSTOM_ITEMS],
			"pvf:tag:1" => [0, FilterSetting::FILTER_NONE],

			"pvf:ck:2:emerald" => [5, FilterSetting::FILTER_NONE],
			"pvf:ck:2:diamond" => [5, FilterSetting::FILTER_NONE],
			"pvf:ck:2:gold" => [5, FilterSetting::FILTER_NONE],

			"pvf:t:5000" => [0, FilterSetting::FILTER_NONE],
		],
		CrateData::RARITY_DIVINE => [
			"i:gen_booster:64:1000" => [2, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:redeemed_book:1:5" => [0, FilterSetting::FILTER_BOOKS],
			"i:max_book:12:2:1:1" => [0, FilterSetting::FILTER_BOOKS],

			"i:redeemed_book:1:5" => [1, FilterSetting::FILTER_BOOKS],
			"i:max_book:1:1:5" => [1, FilterSetting::FILTER_BOOKS],

			"i:gummy_orb:4:5:250" => [3, FilterSetting::FILTER_PET_ITEMS],
			"i:gummy_orb:8:5:500" => [4, FilterSetting::FILTER_PET_ITEMS],
			"i:energy_booster:4:5:25" => [3, FilterSetting::FILTER_PET_ITEMS],
			"i:energy_booster:8:5:50" => [4, FilterSetting::FILTER_PET_ITEMS],

			"pvf:kp:1:large" => [1, FilterSetting::FILTER_NONE],
			"pvf:ck:1:divine" => [2, FilterSetting::FILTER_NONE],
			"pvf:ck:1:divine" => [3, FilterSetting::FILTER_NONE],
			"pvf:ck:50:emerald" => [4, FilterSetting::FILTER_NONE],
			"pvf:ck:75:diamond" => [4, FilterSetting::FILTER_NONE],

			"pvf:ck:2:divine" => [5, FilterSetting::FILTER_NONE],
			"pvf:kp:1:extra-large" => [5, FilterSetting::FILTER_NONE],

			"pvf:t:100000" => [4, FilterSetting::FILTER_NONE],
		],
		CrateData::RARITY_VOTE => [
			"i:essence_of_ascension:1:1" => [0, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:essence_of_ascension:1:2" => [0, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:essence_of_ascension:1:3" => [1, FilterSetting::FILTER_CUSTOM_ITEMS],
			"i:essence_of_ascension:1:4" => [2, FilterSetting::FILTER_CUSTOM_ITEMS],
		]
	];
}
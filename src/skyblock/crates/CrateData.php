<?php namespace skyblock\crates;

class CrateData{

	const SPAWN_WORLD = "scifi1";

	const CRATE_LOCATIONS = [
		0 => [-14715, 121, 13495, self::SPAWN_WORLD, 225, self::CRATE_IRON],
		1 => [-14718, 121, 13491, self::SPAWN_WORLD, 270, self::CRATE_GOLD],
		2 => [-14718, 121, 13488, self::SPAWN_WORLD, 270, self::CRATE_DIAMOND],
		3 => [-14718, 121, 13485, self::SPAWN_WORLD, 270, self::CRATE_EMERALD],
		4 => [-14715, 121, 13481, self::SPAWN_WORLD, 315, self::CRATE_VOTE],
		5 => [-14711, 121, 13478, self::SPAWN_WORLD, 0, self::CRATE_EMERALD],
		6 => [-14708, 121, 13478, self::SPAWN_WORLD, 0, self::CRATE_DIAMOND],
		7 => [-14705, 121, 13478, self::SPAWN_WORLD, 0, self::CRATE_GOLD],
		8 => [-14701, 121, 13481, self::SPAWN_WORLD, 45, self::CRATE_IRON],

		9 => [-14708, 121, 13488, self::SPAWN_WORLD, -45, self::CRATE_DIVINE],
	];

	const CRATE_TYPES = [
		0 => self::CRATE_IRON,
		1 => self::CRATE_GOLD,
		2 => self::CRATE_DIAMOND,
		3 => self::CRATE_EMERALD,
		4 => self::CRATE_VOTE,
		5 => self::CRATE_DIVINE,
	];

	const CRATE_IRON = 0;
	const CRATE_GOLD = 1;
	const CRATE_DIAMOND = 2;
	const CRATE_EMERALD = 3;
	const CRATE_VOTE = 4;
	const CRATE_DIVINE = 5;

	const RARITY_COMMON = 0;
	const RARITY_UNCOMMON = 1;
	const RARITY_RARE = 2;
	const RARITY_LEGENDARY = 3;
	const RARITY_DIVINE = 4;
	const RARITY_VOTE = 5;

}
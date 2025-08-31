<?php

namespace skyblock\generators;

use skyblock\generators\tile\OreGenerator;

class Structure{

	const TYPE_ORE_GENERATOR = 0;
	const TYPE_DIMENSIONAL_GENERATOR = 1;

	const UPGRADE_COSTS = [
		self::TYPE_ORE_GENERATOR => [
			OreGenerator::TYPE_COAL => [
				2 => 1000,
				3 => 2000,
				4 => 3000,
				5 => 5000,
				6 => 7500,
				7 => 10000,
				8 => 15000
			],
			OreGenerator::TYPE_IRON => [
				2 => 5000,
				3 => 10000,
				4 => 10000,
				5 => 25000,
				6 => 50000,
				7 => 50000,
				8 => 75000
			],
			OreGenerator::TYPE_REDSTONE => [
				2 => 10000,
				3 => 15000,
				4 => 20000,
				5 => 25000,
				6 => 50000,
				7 => 75000,
				8 => 100000
			],
			OreGenerator::TYPE_LAPIS_LAZULI => [
				2 => 25000,
				3 => 50000,
				4 => 75000,
				5 => 100000,
				6 => 100000,
				7 => 250000,
				8 => 250000
			],
			OreGenerator::TYPE_COPPER => [
				2 => 50000,
				3 => 75000,
				4 => 100000,
				5 => 100000,
				6 => 250000,
				7 => 250000,
				8 => 300000
			],
			OreGenerator::TYPE_GOLD => [
				2 => 100000,
				3 => 100000,
				4 => 250000,
				5 => 250000,
				6 => 500000,
				7 => 500000,
				8 => 750000
			],
			OreGenerator::TYPE_DIAMOND => [
				2 => 100000,
				3 => 250000,
				4 => 250000,
				5 => 500000,
				6 => 750000,
				7 => 750000,
				8 => 1000000
			],
			OreGenerator::TYPE_EMERALD => [
				2 => 250000,
				3 => 500000,
				4 => 500000,
				5 => 750000,
				6 => 1000000,
				7 => 1000000,
				8 => 2000000
			],
			OreGenerator::TYPE_OBSIDIAN => [
				2 => 500000,
				3 => 500000,
				4 => 750000,
				5 => 1000000,
				6 => 1000000,
				7 => 1000000,
				8 => 1500000
			],
			OreGenerator::TYPE_GLOWING_OBSIDIAN => [
				2 => 500000,
				3 => 500000,
				4 => 750000,
				5 => 1000000,
				6 => 1000000,
				7 => 1000000,
				8 => 1500000
			],
			OreGenerator::TYPE_ANCIENT_DEBRIS => [
				2 => 750000,
				3 => 750000,
				4 => 1000000,
				5 => 1000000,
				6 => 1500000,
				7 => 2000000,
				8 => 2500000
			],
			OreGenerator::TYPE_GILDED_OBSIDIAN => [
				2 => 750000,
				3 => 750000,
				4 => 1000000,
				5 => 1000000,
				6 => 1500000,
				7 => 2000000,
				8 => 2500000
			]
		],
		self::TYPE_DIMENSIONAL_GENERATOR => [
			2 => 200000,
			3 => 500000,
			4 => 500000,
			5 => 750000,
			6 => 750000,
			7 => 1000000,
			8 => 1000000
		]
	];

	const RATES = [
		self::TYPE_ORE_GENERATOR => [
			1 => 10,
			2 => 9,
			3 => 8,
			4 => 7,
			5 => 6,
			6 => 5,
			7 => 4,
			8 => 3
		],
		self::TYPE_DIMENSIONAL_GENERATOR => [
			1 => 10,
			2 => 9,
			3 => 8,
			4 => 7,
			5 => 6,
			6 => 5,
			7 => 4,
			8 => 3
		]
	];

	const EXTENDER = [
		0 => 1,
		1 => 2,
		2 => 3
	];
}
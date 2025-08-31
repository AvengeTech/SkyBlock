<?php namespace skyblock\islands;

use skyblock\islands\world\generator\type\{
	BasicIsland,
	FlatTopIsland
};

class Structure{

	const ISLAND_BASIC = 0;
	const ISLAND_FLAT_TOP = 1;
	const ISLAND_ONE_BLOCK = 2;

	const ISLANDS = [
		self::ISLAND_BASIC => [
			"name" => "basic",
			"description" => "Basic skyblock island",
			"class" => BasicIsland::class
		],
		self::ISLAND_FLAT_TOP => [
			"name" => "flat top",
			"description" => "Island wit dah flat top",
			"class" => FlatTopIsland::class
		],
	];

}
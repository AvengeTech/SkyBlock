<?php

namespace skyblock\farming;

class Structure{

	const MAX_LEVEL = 12;

	public const CROP_LEVEL = [
		0 => "sugar_cane",
		1 => "cactus",
		2 => "wheat_block",
		3 => "potato",
		4 => "carrots",
		5 => "beetroots",
		6 => "pumpkin",
		7 => "melon_block",
		8 => "nether_wart",
		9 => "bamboo",
		10 => "red_mushroom_block",
		11 => "brown_mushroom_block",
		12 => "chorus_plant",
	];

	public static function getLevel(string $name) : int{
		foreach(self::CROP_LEVEL as $key => $value){
			if($value === $name) return $key;
		}

		return -1;
	}

	public static function getNextCrop(int $level) : string{
		$level++;

		$crop =  self::CROP_LEVEL[$level];

		return match($crop){
			"wheat_block" => "wheat",
			"melon_block" => "melon",
			"red_mushroom_block" => "red_mushroom",
			"brown_mushroom_block" => "brown_mushroom",
			"chorus_plant" => "chorus_fruit",
			default => $crop
		};
	}
}
<?php

declare(strict_types=1);

namespace skyblock\enchantments\utils;

use skyblock\enchantments\EnchantmentData as ED;
use skyblock\enchantments\type\Enchantment;

class EnchantmentChances{

	// Base Chance is mulitplied by level
	public const BASE_CHANCES = [
		ED::ABSORB => 6.66,
		ED::BLESSING => 3.0,
		ED::DODGE => 5.0,
		ED::ELECTRIFY => 4.0,
		ED::FEED => 20.0,
		ED::FORESIGHT => 8,
		ED::KABOOM => 4.0,
		ED::METAL_DETECTOR => 2.25,
		ED::OOF => 33.33,
		ED::PIERCE => 7.5,
		ED::RAGE => 5.0,
		ED::SNARE => 7,
		ED::TIDES => 6.0,
		ED::UPLIFT => 20.0,
		ED::ZEUS => 4.15,
	];

	// Set chances for each level of an enchantment
	public const SET_CHANCES = [
		ED::ANTI_KNOCKBACK => [
			1 => 8.33,
			2 => 11.11,
			3 => 16.66,
		],
		ED::BLEED => [
			1 => 6.25,
			2 => 10,
			3 => 16.66,
			4 => 25
		],
		ED::BURROW => [
			1 => 3.5,
			2 => 5,
			3 => 7.5,
			4 => 10,
			5 => 12.5
		],
		ED::CAPSULE => [
			1 => 3.5,
			2 => 6.5,
			3 => 9.5,
		],
		ED::CROUCH => [
			1 => 6,
			2 => 12,
			3 => 15,
			4 => 18
		],
		ED::DAZE => [
			1 => 6.66,
			2 => 10.0,
			3 => 14.33
		],
		ED::DECAY => [
			1 => 4,
			2 => 6.25,
			3 => 8.33,
			4 => 11.11
		],
		ED::FEATHER_WEIGHT => [
			1 => 8.33,
			2 => 11.11,
			3 => 16.67,
			4 => 20.0,
		],
		ED::FIREBALL => [
			1 => 5,
			2 => 6.25,
			3 => 8.33,
			4 => 11.11
		],
		ED::HADES => [
			1 => 5,
			2 => 9,
			3 => 13,
			4 => 16
		],
		ED::KEY_THEFT => [
			1 => 6.66,
			2 => 10,
			3 => 20
		],
		ED::LIFESTEAL => [
			1 => 5,
			2 => 7.69,
			3 => 10
		],
		ED::MAGNIFY => [
			1 => 10.0,
			2 => 12.5,
			3 => 20.0,
			4 => 33.33
		],
		ED::PURIFY => [ // find a better way.
			1 => 10.0,
			2 => 10.0,
			3 => 10.0,
			4 => 10.0,
		],
		ED::RADIATION => [
			1 => 5,
			2 => 6.66,
			3 => 12.5
		],
		ED::SIFT => [
			50.0,
			33.33,
			25.0,
			20.0
		],
		ED::SHUFFLE => [
			1 => 5,
			2 => 5.55,
			3 => 7.69
		],
		ED::SORCERY => [
			1 => 4,
			2 => 9,
			3 => 12
		],
		ED::SPITE => [
			1 => 2,
			2 => 5,
			3 => 7,
			4 => 10
		],
		ED::STARVATION => [
			1 => 3.33,
			2 => 4,
			3 => 6.66,
			4 => 8.33
		],
		ED::TECH_BLAST => [
			1 => 4,
			2 => 5.55,
			3 => 7.69
		],
		ED::TRANSFUSION => [
			1 => 6.0,
			2 => 12.0,
			3 => 15.0,
			4 => 18.0,
			5 => 20.0
		],
	];

	public static function hasChance(Enchantment $enchantment, float $chance = 0) : bool{
		if($chance > 0) return EnchantmentUtils::getRandomChance() <= $chance;

		$level = $enchantment->getStoredLevel();

		if(isset(self::BASE_CHANCES[$enchantment->getId()])){
			return EnchantmentUtils::getRandomChance() <= self::BASE_CHANCES[$enchantment->getId()] * $level;
		}elseif(isset(self::SET_CHANCES[$enchantment->getId()])){
			return EnchantmentUtils::getRandomChance() <= (self::SET_CHANCES[$enchantment->getId()][$level] ?? self::SET_CHANCES[$enchantment->getId()][1] * $level ?? 100);
		}

		return true;
	}
}
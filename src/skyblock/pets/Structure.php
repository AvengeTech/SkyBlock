<?php

namespace skyblock\pets;

use skyblock\pets\types\island\AllayPet;
use skyblock\pets\types\island\AxolotlPet;
use skyblock\pets\types\island\BeePet;
use skyblock\pets\types\island\CatPet;
use skyblock\pets\types\island\DogPet;
use skyblock\pets\types\island\FoxPet;
use skyblock\pets\types\island\RabbitPet;
use skyblock\pets\types\island\VexPet;

class Structure{

	const DATA_NAME = "name";
	const DATA_CLASS = "class";
	const DATA_RARITY = "rarity";
	const DATA_MAX_LEVEL = "max-level";
	const DATA_BUFFS = "buffs";
	const DATA_ENERGY_DEPLETION = "energy-depletion";
	const DATA_ENERGY_REGAIN = "energy-regain";
	const DATA_BUFF_DESCRIPTION = "buff-description";
	const DATA_BUFF_CHANCES = "buff-chances";

	const RARITY_COMMON = 1;
	const RARITY_UNCOMMON = 2;
	const RARITY_RARE = 3;
	const RARITY_LEGENDARY = 4;
	const RARITY_DIVINE = 5;

	const ISLAND_PET = 0;
	const COMBAT_PET = 1;

	const PETS = [
		self::ALLAY => [
			self::DATA_NAME => "Allay",
			self::DATA_CLASS => AllayPet::class,
			self::DATA_MAX_LEVEL => 50,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_BUFFS => [
				1 => [
					self::DATA_BUFF_CHANCES => [2.5],
					self::DATA_BUFF_DESCRIPTION => "2.5% chance to double drops from a mob"
				],
				10 => [
					self::DATA_BUFF_CHANCES => [7.5],
					self::DATA_BUFF_DESCRIPTION => "7.5% chance to double drops from a mob"
				],
				20 => [
					self::DATA_BUFF_CHANCES => [12.5],
					self::DATA_BUFF_DESCRIPTION => "12.5% chance to double drops from a mob"
				],
				30 => [
					self::DATA_BUFF_CHANCES => [15.5],
					self::DATA_BUFF_DESCRIPTION => "15.5% chance to double drops from a mob"
				],
				40 => [
					self::DATA_BUFF_CHANCES => [16.5, 3.75],
					self::DATA_BUFF_DESCRIPTION => "15.5% chance to double drops and 2.75% chance to triple drops from a mob"
				],
				50 => [
					self::DATA_BUFF_CHANCES => [18.5, 6.45],
					self::DATA_BUFF_DESCRIPTION => "15.5% chance to double drops and 6.45% chance to triple drops from a mob"
				]
			]
		],
		self::AXOLOTL => [
			self::DATA_NAME => "Axolotl",
			self::DATA_CLASS => AxolotlPet::class,
			self::DATA_MAX_LEVEL => 50,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_BUFFS => [
				1 => [
					self::DATA_BUFF_CHANCES => [3.5],
					self::DATA_BUFF_DESCRIPTION => "3.5% chance to double the fish caught"
				],
				10 => [
					self::DATA_BUFF_CHANCES => [7.5],
					self::DATA_BUFF_DESCRIPTION => "7.5% chance to double the fish caught"
				],
				20 => [
					self::DATA_BUFF_CHANCES => [13.45],
					self::DATA_BUFF_DESCRIPTION => "13.45% chance to double the fish caught"
				],
				30 => [
					self::DATA_BUFF_CHANCES => [15, 2.5],
					self::DATA_BUFF_DESCRIPTION => "15% chance to double or 2.5% chance to triple the fish caught"
				],
				40 => [
					self::DATA_BUFF_CHANCES => [18.25, 7.5],
					self::DATA_BUFF_DESCRIPTION => "19.25% chance to double or 7.5% chance to triple the fish caught"
				],
				50 => [
					self::DATA_BUFF_CHANCES => [22.5, 12.5],
					self::DATA_BUFF_DESCRIPTION => "22.5% chance to double or 12.5% chance to triple the fish caught"
				]
			]
		],
		self::BEE => [
			self::DATA_NAME => "Bee",
			self::DATA_CLASS => BeePet::class,
			self::DATA_MAX_LEVEL => 50,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_BUFFS => [
				1 => [
					self::DATA_BUFF_CHANCES => [2.5],
					self::DATA_BUFF_DESCRIPTION => "2.5% chance to upgrade the harvested crop"
				],
				10 => [
					self::DATA_BUFF_CHANCES => [7.5],
					self::DATA_BUFF_DESCRIPTION => "7.5% chance to upgrade the harvested crop"
				],
				20 => [
					self::DATA_BUFF_CHANCES => [12.5],
					self::DATA_BUFF_DESCRIPTION => "12.5% chance to upgrade the harvested crop"
				],
				30 => [
					self::DATA_BUFF_CHANCES => [15, 5],
					self::DATA_BUFF_DESCRIPTION => "15% harvest upgrade chance and 5% chance for 1-2 extra harvested crops at max level"
				],
				40 => [
					self::DATA_BUFF_CHANCES => [15, 7.5],
					self::DATA_BUFF_DESCRIPTION => "15% harvest upgrade chance and 7.5% chance for 1-2 extra harvested crops at max level"
				],
				50 => [
					self::DATA_BUFF_CHANCES => [15, 10],
					self::DATA_BUFF_DESCRIPTION => "15% harvest upgrade chance and 10% chance for 1-2 extra harvested crops at max level"
				]
			]
		],
		self::CAT => [
			self::DATA_NAME => "Cat",
			self::DATA_CLASS => CatPet::class,
			self::DATA_MAX_LEVEL => 50,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_BUFFS => [
				1 => [
					self::DATA_BUFF_CHANCES => [2.5],
					self::DATA_BUFF_DESCRIPTION => "Increases the chance to get a fish on the line by 2.5%"
				],
				10 => [
					self::DATA_BUFF_CHANCES => [5],
					self::DATA_BUFF_DESCRIPTION => "Increases the chance to get a fish on the line by 5%"
				],
				20 => [
					self::DATA_BUFF_CHANCES => [10],
					self::DATA_BUFF_DESCRIPTION => "Increases the chance to get a fish on the line by 10%"
				],
				30 => [
					self::DATA_BUFF_CHANCES => [20],
					self::DATA_BUFF_DESCRIPTION => "Increases the chance to get a fish on the line by 20%"
				],
				40 => [
					self::DATA_BUFF_CHANCES => [25],
					self::DATA_BUFF_DESCRIPTION => "Increases the chance to get a fish on the line by 25%"
				],
				50 => [
					self::DATA_BUFF_CHANCES => [30],
					self::DATA_BUFF_DESCRIPTION => "Increases the chance to get a fish on the line by 30%"
				]
			]
		],
		self::DOG => [
			self::DATA_NAME => "Dog",
			self::DATA_CLASS => DogPet::class,
			self::DATA_MAX_LEVEL => 50,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_BUFFS => [
				1 => [
					self::DATA_BUFF_CHANCES => [2.5],
					self::DATA_BUFF_DESCRIPTION => "2.5% chance to double the ore mined"
				],
				10 => [
					self::DATA_BUFF_CHANCES => [5],
					self::DATA_BUFF_DESCRIPTION => "5% chance to double the ore mined"
				],
				20 => [
					self::DATA_BUFF_CHANCES => [10],
					self::DATA_BUFF_DESCRIPTION => "10% chance to double the ore mined"
				],
				30 => [
					self::DATA_BUFF_CHANCES => [15, 10],
					self::DATA_BUFF_DESCRIPTION => "15% chance to double and 10% chance triple the ore mined"
				],
				40 => [
					self::DATA_BUFF_CHANCES => [20, 10],
					self::DATA_BUFF_DESCRIPTION => "20% chance to double and 10% chance triple the ore mined"
				],
				50 => [
					self::DATA_BUFF_CHANCES => [25, 10],
					self::DATA_BUFF_DESCRIPTION => "25% chance to double and 10% chance triple the ore mined"
				]
			]
		],
		self::FOX => [
			self::DATA_NAME => "Fox",
			self::DATA_CLASS => FoxPet::class,
			self::DATA_MAX_LEVEL => 50,
			self::DATA_RARITY => self::RARITY_DIVINE,
			self::DATA_BUFFS => [
				1 => [
					self::DATA_BUFF_CHANCES => [2.5],
					self::DATA_BUFF_DESCRIPTION => "2.5% to upgrade the mined ore to its block form"
				],
				10 => [
					self::DATA_BUFF_CHANCES => [5],
					self::DATA_BUFF_DESCRIPTION => "5% to upgrade the mined ore to its block form"
				],
				20 => [
					self::DATA_BUFF_CHANCES => [7.5],
					self::DATA_BUFF_DESCRIPTION => "7.5% to upgrade the mined ore to its block form"
				],
				30 => [
					self::DATA_BUFF_CHANCES => [10, 1.2],
					self::DATA_BUFF_DESCRIPTION => "10% to upgrade the mined ore to its block form and sell for 1.2x more"
				],
				40 => [
					self::DATA_BUFF_CHANCES => [12.5, 1.4],
					self::DATA_BUFF_DESCRIPTION => "12.5% to upgrade the mined ore to its block form and sell for 1.4x more"
				],
				50 => [
					self::DATA_BUFF_CHANCES => [15, 1.6],
					self::DATA_BUFF_DESCRIPTION => "15% to upgrade the mined ore to its block form and sell for 1.6x more"
				]
			]
		],
		self::RABBIT => [
			self::DATA_NAME => "Rabbit",
			self::DATA_CLASS => RabbitPet::class,
			self::DATA_MAX_LEVEL => 50,
			self::DATA_RARITY => self::RARITY_LEGENDARY,
			self::DATA_BUFFS => [
				1 => [
					self::DATA_BUFF_CHANCES => [1.15],
					self::DATA_BUFF_DESCRIPTION => "Increases the sell value of crops by 1.15x"
				],
				10 => [
					self::DATA_BUFF_CHANCES => [1.35],
					self::DATA_BUFF_DESCRIPTION => "Increases the sell value of crops by 1.35x"
				],
				20 => [
					self::DATA_BUFF_CHANCES => [1.55],
					self::DATA_BUFF_DESCRIPTION => "Increases the sell value of crops by 1.55x"
				],
				30 => [
					self::DATA_BUFF_CHANCES => [1.75],
					self::DATA_BUFF_DESCRIPTION => "Increases the sell value of crops by 1.75x"
				],
				40 => [
					self::DATA_BUFF_CHANCES => [1.95],
					self::DATA_BUFF_DESCRIPTION => "Increases the sell value of crops by 1.95x"
				],
				50 => [
					self::DATA_BUFF_CHANCES => [2.05],
					self::DATA_BUFF_DESCRIPTION => "Increases the sell value of crops by 2.05x"
				]
			]
		],
		self::VEX => [
			self::DATA_NAME => "Vex",
			self::DATA_CLASS => VexPet::class,
			self::DATA_MAX_LEVEL => 50,
			self::DATA_RARITY => self::RARITY_RARE,
			self::DATA_BUFFS => [
				1 => [
					self::DATA_BUFF_CHANCES => [7.5],
					self::DATA_BUFF_DESCRIPTION => "Increases player damage by 7.5% when attacking mobs"
				],
				10 => [
					self::DATA_BUFF_CHANCES => [15],
					self::DATA_BUFF_DESCRIPTION => "Increases player damage by 15% when attacking mobs"
				],
				20 => [
					self::DATA_BUFF_CHANCES => [22.5],
					self::DATA_BUFF_DESCRIPTION => "Increases player damage by 22.5% when attacking mobs"
				],
				30 => [
					self::DATA_BUFF_CHANCES => [30, 1.15],
					self::DATA_BUFF_DESCRIPTION => "Increases player damage by 30% when attacking mobs and increases the sell value of mob drops by 1.15x"
				],
				40 => [
					self::DATA_BUFF_CHANCES => [35, 1.35],
					self::DATA_BUFF_DESCRIPTION => "Increases player damage by 35% when attacking mobs and increases the sell value of mob drops by 1.35x"
				],
				50 => [
					self::DATA_BUFF_CHANCES => [40, 1.5],
					self::DATA_BUFF_DESCRIPTION => "Increases player damage by 40% when attacking mobs and increases the sell value of mob drops by 1.5x"
				]
			]
		]
	];

	const MAX_ENERGY = [
		self::RARITY_COMMON => 10000,
		self::RARITY_UNCOMMON => 10000,
		self::RARITY_RARE => 50,
		self::RARITY_LEGENDARY => 75,
		self::RARITY_DIVINE => 120,
	];

	const ENERGY_REGAIN = [
		self::RARITY_COMMON => 0,
		self::RARITY_UNCOMMON => 0,
		self::RARITY_RARE => 1,
		self::RARITY_LEGENDARY => 1.25,
		self::RARITY_DIVINE => 1.5,
	];

	const ENERGY_DEPLETION = [
		self::RARITY_COMMON => 0,
		self::RARITY_UNCOMMON => 0,
		self::RARITY_RARE => 7.5,
		self::RARITY_LEGENDARY => 10,
		self::RARITY_DIVINE => 12.5,
	];

	const ALLAY = 1;
	const AXOLOTL = 2;
	const BEE = 3;
	const CAT = 4;
	const DOG = 5;
	const FOX = 6;
	const RABBIT = 7;
	const VEX = 8;
}
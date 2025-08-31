<?php

namespace skyblock\item;

use core\utils\TextFormat as TF;

class Structure{

	const DATA_NAME = "name";
	const DATA_DESCRIPTION = "description";

	const INVENTORY_ITEMS = [
		self::PET_EGG => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Pet Egg",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "Shane has a big forehead"
			]
		],
		self::REDEEMED_BOOK => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Enchantment Book",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "Shane has the biggest forehead"
			]
		],
		self::ESSENCE_OF_SUCCESS => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Essence Selection",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "Shane has a biggester forehead"
			]
		],
		self::SOLIDIFIER => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Solidifer",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "Shane has the biggerest forehead"
			]
		],
		self::HORIZONTAL_EXTENDER => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Horizontal Extender",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "Shane's forehead is not small"
			]
		],
		self::VERTICAL_EXTENDER => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Vertical Extender",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "Shane's forehead grows everyday"
			]
		],
		self::PET_BOX => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Pet Box",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "Shane just has a big forehead"
			]
		],
		self::PET_KEY => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Pet Key",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "big forehead small brain"
			]
		],
		self::DEATH_TAG => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Death Tag",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "forehead"
			]
		],
		self::SELL_WAND => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Sell Wand",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "headfore"
			]
		],
		self::NAME_TAG => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Name Tag",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "shane just has a massive forehead"
			]
		],
		self::PET_ENERGY_BOOSTER => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Energy Booster",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "shane loves sockjuice"
			]
		],
		self::PET_GUMMY_ORB => [
			self::DATA_NAME => TF::BOLD . TF::RED . "Gummy Orb",
			self::DATA_DESCRIPTION => [
				" ",
				TF::GRAY . "shane loves hemorrhoids"
			]
		]
	];

	const ANIMATOR = "animator";
	const AUTO_MINER = "auto_miner";
	const DEATH_TAG = "death_tag"; // added
	const DIMENSONAL_GENERATOR = "dimensonal_generator";
	const ESSENCE_OF_SUCCESS = "essence_of_success"; // added as Essence Selection
	const GEN_BOOSTER = "gen_booster";
	const HORIZONTAL_EXTENDER = "horizontal_extender"; // added
	const KEY_NOTE = "key_note";
	const MAX_BOOK = "max_book";
	const MOB_SPAWNER = "mob_spawner";
	const NAME_TAG = "name_tag"; // added
	const ORE_GENERATOR = "ore_generator";
	const PET_ENERGY_BOOSTER = "energy_booster"; // added
	const PET_GUMMY_ORB = "gummy_orb"; // added
	const PET_BOX = "pet_box"; // added
	const PET_EGG = "pet_egg"; // added
	const PET_KEY = "pet_key"; // added
	const POUCH_OF_ESSENCE = "pouch_of_essence";
	const REDEEMED_BOOK = "redeemed_book"; // added
	const SELL_WAND = "sell_wand"; // added
	const SOLIDIFIER = "solidifier"; // added
	const TECHIT_NOTE = "techit_note";
	const UNBOUND_TOME = "unbound_tome";
	const VERTICAL_EXTENDER = "vertical_extender"; // added
}
<?php namespace skyblock\settings;

interface SkyBlockSettings{

	const VERSION = "1.0.0";

	//Normal
	const RAINBOW_BOSS_BAR = 1;
	const NO_TOOL_DROP = 2;
	const TOOL_BREAK_ALERT = 3;
	const LIGHTNING = 4;
	const AUTO_INV = 5;
	const AUTO_XP = 6;
	
	const ISLAND_CHAT = 7;
	const DEFAULT_ISLAND = 8;

	//Premium
	const AUTO_ISLAND_FLIGHT = 20;

	const DEFAULT_SETTINGS = [
		self::RAINBOW_BOSS_BAR => true,
		self::NO_TOOL_DROP => false,
		self::TOOL_BREAK_ALERT => true,
		self::LIGHTNING => true,
		self::AUTO_INV => true,
		self::AUTO_XP => true,
		self::ISLAND_CHAT => false,
		self::DEFAULT_ISLAND => "",
		
		self::AUTO_ISLAND_FLIGHT => false,
	];
	
	const SETTING_UPDATES = [

	];

}
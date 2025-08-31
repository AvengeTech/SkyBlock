<?php namespace skyblock\islands\permission;

class Permissions{
	
	const VERSION = "1.0.0";
	
	const OWNER = 0;
	const HIERARCHY = 1;

	const EDIT_BLOCKS = 2;
	const EDIT_ARMOR_STANDS = 3;
	const EDIT_ITEM_FRAMES = 4;
	const EDIT_SPAWNERS = 5;
	const EDIT_GEN_BLOCKS = 6;
	const EDIT_ORE_FROM_ORE_GENS = 7;

	const OPEN_CONTAINERS = 8;
	const OPEN_DOORS = 9;

	const DROP_ITEMS = 10;
	const PICKUP_ITEMS = 11;
	const PICKUP_XP = 12;

	const THROW_XP_BOTTLES = 13;
	const THROW_SNOWBALLS = 14;
	const THROW_ENDER_PEARLS = 15;
	const CAST_FISHING_ROD = 16;

	const KILL_SPAWNER_MOBS = 17;

	const USE_SELL_CHEST = 18;
	const USE_SHOP = 19;
	const USE_FLY = 20;
	const USE_WARPS = 21;
	const USE_SIGN_SHOPS = 22;
	const COMPLETE_CHALLENGES = 23;

	const KICK_VISITORS = 24;
	const EDIT_MEMBERS = 25;
	const EDIT_DEFAULT_PERMISSIONS = 26;
	const EDIT_BLOCK_LIST = 27;
	const EDIT_WARPS = 28;
	const EDIT_WARP_PADS = 29;
	const EDIT_SIGN_SHOPS = 30;
	const EDIT_TEXTS = 31;
	const MOVE_ISLAND_MENU = 32;
	const EDIT_ISLAND = 33;
	
	const DEFAULT_VISITOR_PERMISSIONS = [
		self::OWNER => false,
		self::HIERARCHY => 5,
		
		self::EDIT_BLOCKS => false,
		self::EDIT_ARMOR_STANDS => false,
		self::EDIT_ITEM_FRAMES => false,
		self::EDIT_SPAWNERS => false,
		self::EDIT_GEN_BLOCKS => false,
		self::EDIT_ORE_FROM_ORE_GENS => true,

		self::OPEN_CONTAINERS => false,
		self::OPEN_DOORS => false,

		self::DROP_ITEMS => true,
		self::PICKUP_ITEMS => true,
		self::PICKUP_XP => true,

		self::THROW_XP_BOTTLES => true,
		self::THROW_SNOWBALLS => true,
		self::THROW_ENDER_PEARLS => true,
		self::CAST_FISHING_ROD => false,

		self::KILL_SPAWNER_MOBS => true,

		self::USE_SELL_CHEST => false,
		self::USE_SHOP => false,
		self::USE_FLY => true,
		self::USE_WARPS => true,
		self::USE_SIGN_SHOPS => false,
		self::COMPLETE_CHALLENGES => false,

		self::KICK_VISITORS => false,
		self::EDIT_MEMBERS => false,
		self::EDIT_DEFAULT_PERMISSIONS => false,
		self::EDIT_BLOCK_LIST => false,
		self::EDIT_WARPS => false,
		self::EDIT_WARP_PADS => false,
		self::EDIT_SIGN_SHOPS => false,
		self::EDIT_TEXTS => false,
		self::MOVE_ISLAND_MENU => false,
		self::EDIT_ISLAND => false,
	];

	const UNLOCKED_PERMISSIONS = [
		self::OWNER => true,
		self::HIERARCHY => PHP_INT_MAX,

		self::EDIT_BLOCKS => true,
		self::EDIT_ARMOR_STANDS => true,
		self::EDIT_ITEM_FRAMES => true,
		self::EDIT_SPAWNERS => true,
		self::EDIT_GEN_BLOCKS => true,
		self::EDIT_ORE_FROM_ORE_GENS => true,

		self::OPEN_CONTAINERS => true,
		self::OPEN_DOORS => true,

		self::DROP_ITEMS => true,
		self::PICKUP_ITEMS => true,
		self::PICKUP_XP => true,

		self::THROW_XP_BOTTLES => true,
		self::THROW_SNOWBALLS => true,
		self::THROW_ENDER_PEARLS => true,
		self::CAST_FISHING_ROD => true,

		self::KILL_SPAWNER_MOBS => true,

		self::USE_SELL_CHEST => true,
		self::USE_SHOP => true,
		self::USE_FLY => true,
		self::USE_WARPS => true,
		self::USE_SIGN_SHOPS => true,
		self::COMPLETE_CHALLENGES => true,

		self::KICK_VISITORS => true,
		self::EDIT_MEMBERS => true,
		self::EDIT_DEFAULT_PERMISSIONS => true,
		self::EDIT_BLOCK_LIST => true,
		self::EDIT_WARPS => true,
		self::EDIT_WARP_PADS => true,
		self::EDIT_SIGN_SHOPS => true,
		self::EDIT_TEXTS => true,
		self::MOVE_ISLAND_MENU => true,
		self::EDIT_ISLAND => true,
	];

	const VISITOR_PERMISSION_UPDATES = [

	];
	
	const DEFAULT_INVITE_PERMISSIONS = [
		self::OWNER => false,
		self::HIERARCHY => 5,

		self::EDIT_BLOCKS => true,
		self::EDIT_ARMOR_STANDS => true,
		self::EDIT_ITEM_FRAMES => true,
		self::EDIT_SPAWNERS => false,
		self::EDIT_GEN_BLOCKS => false,
		self::EDIT_ORE_FROM_ORE_GENS => true,

		self::OPEN_CONTAINERS => false,
		self::OPEN_DOORS => true,

		self::DROP_ITEMS => true,
		self::PICKUP_ITEMS => true,
		self::PICKUP_XP => true,

		self::THROW_XP_BOTTLES => true,
		self::THROW_SNOWBALLS => true,
		self::THROW_ENDER_PEARLS => true,
		self::CAST_FISHING_ROD => true,

		self::KILL_SPAWNER_MOBS => true,

		self::USE_SELL_CHEST => false,
		self::USE_SHOP => true,
		self::USE_FLY => true,
		self::USE_WARPS => true,
		self::USE_SIGN_SHOPS => true,
		self::COMPLETE_CHALLENGES => true,

		self::KICK_VISITORS => false,
		self::EDIT_MEMBERS => false,
		self::EDIT_DEFAULT_PERMISSIONS => false,
		self::EDIT_BLOCK_LIST => false,
		self::EDIT_WARPS => false,
		self::EDIT_WARP_PADS => false,
		self::EDIT_SIGN_SHOPS => false,
		self::EDIT_TEXTS => false,
		self::MOVE_ISLAND_MENU => false,
		self::EDIT_ISLAND => false,
	];
	
	const INVITE_PERMISSION_UPDATES = [
		
	];
	
}
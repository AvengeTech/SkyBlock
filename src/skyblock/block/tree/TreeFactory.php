<?php namespace skyblock\block\tree;

use pocketmine\utils\Random;
use pocketmine\world\generator\object\{
	TreeFactory as PMTF,
	TreeType,
	Tree,
	OakTree,
	SpruceTree,
	JungleTree,
	AcaciaTree,
	BirchTree
};

class TreeFactory{

	public static function get(Random $random, ?TreeType $type = null) : ?Tree{
		return match($type){
			null, TreeType::OAK => new OakTree(), //TODO: big oak has a 1/10 chance
			TreeType::SPRUCE => new SpruceTree(),
			TreeType::JUNGLE => new JungleTree(),
			TreeType::ACACIA => new AcaciaTree(),
			TreeType::BIRCH => new BirchTree($random->nextBoundedInt(39) === 0),
			TreeType::DARK_OAK => new DarkOakTree(),
			default => null,
		};
	}

}
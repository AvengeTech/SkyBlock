<?php namespace skyblock\block\tree;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\generator\object\Tree;

class DarkOakTree extends Tree{

	public function __construct(){
		parent::__construct(VanillaBlocks::DARK_OAK_LOG(), VanillaBlocks::DARK_OAK_LEAVES());
	}

}
<?php namespace skyblock\crates\entity;

use core\utils\TextFormat;

class DiamondCrate extends Crate{

	public function getType() : string{
		return "diamond";
	}

	public function getTypeVariant() : int{
		return 2;
	}

	public function getNameFormat() : string{
		return TextFormat::BOLD . TextFormat::AQUA . "DIAMOND CRATE";
	}

	public function getRandomRarity() : int{
		$chance = mt_rand(0,100);

		$rarity = 0;

		if($chance <= 60) $rarity = 2;
		if($chance > 60) $rarity = 3;

		return $rarity;
	}
	
}
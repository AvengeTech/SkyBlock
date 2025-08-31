<?php namespace skyblock\crates\entity;

use core\utils\TextFormat;

class IronCrate extends Crate{

	public function getType() : string{
		return "iron";
	}

	public function getTypeVariant() : int{
		return 0;
	}

	public function getNameFormat() : string{
		return TextFormat::BOLD . TextFormat::WHITE . "IRON CRATE";
	}

	public function getRandomRarity() : int{
		$chance = mt_rand(0,100);

		$rarity = 0;

		if($chance <= 40) $rarity = 0;
		if($chance > 40 && $chance <= 70) $rarity = 1;
		if($chance > 70 && $chance <= 90) $rarity = 2;
		if($chance > 90) $rarity = 3;

		return $rarity;
	}
}
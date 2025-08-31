<?php namespace skyblock\crates\entity;

use core\utils\TextFormat;

class GoldCrate extends Crate{

	public function getType() : string{
		return "gold";
	}

	public function getTypeVariant() : int{
		return 1;
	}

	public function getNameFormat() : string{
		return TextFormat::BOLD . TextFormat::GOLD . "GOLD CRATE";
	}

	public function getRandomRarity() : int{
		$chance = mt_rand(0,100);

		$rarity = 0;

		if($chance <= 20) $rarity = 0;
		if($chance > 20 && $chance < 75) $rarity = 1;
		if($chance >= 75 && $chance <= 90) $rarity = 2;
		if($chance > 90) $rarity = 3;

		return $rarity;
	}
	
}
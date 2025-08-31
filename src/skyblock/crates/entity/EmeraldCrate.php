<?php namespace skyblock\crates\entity;

use core\utils\TextFormat;

class EmeraldCrate extends Crate{

	public function getType() : string{
		return "emerald";
	}

	public function getTypeVariant() : int{
		return 3;
	}

	public function getNameFormat() : string{
		return TextFormat::BOLD . TextFormat::GREEN . "EMERALD CRATE";
	}

	public function getRandomRarity() : int{
		return mt_rand(0, 100) <= 80 ? 3 : 2;
	}
	
}
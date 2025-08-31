<?php namespace skyblock\crates\entity;

use core\utils\TextFormat;

class DivineCrate extends Crate{

	public function getType() : string{
		return "divine";
	}

	public function getTypeVariant() : int{
		return 5;
	}

	public function getNameFormat() : string{
		return TextFormat::BOLD . TextFormat::RED . "DIVINE CRATE";
	}

	public function getNoKeyMessage() : string{
		return TextFormat::RI . "You don't have any " . $this->getType() . " keys to open! Get more by prestiging your tools, or every 5 island levels past level 15";
	}

	public function getRandomRarity() : int{
		return 4;
	}

}
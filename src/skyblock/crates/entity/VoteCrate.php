<?php namespace skyblock\crates\entity;

use core\utils\TextFormat;
use skyblock\crates\CrateData;

class VoteCrate extends Crate{

	public function getType() : string{
		return "vote";
	}

	public function getTypeVariant() : int{
		return 4;
	}

	public function getNameFormat() : string{
		return TextFormat::BOLD . TextFormat::YELLOW . "VOTE CRATE";
	}

	public function getNoKeyMessage() : string{
		return TextFormat::RI . "You don't have any " . $this->getType() . " keys to open! Find out how to get more by typing " . TextFormat::YELLOW . "/vote" . TextFormat::GRAY . " in chat!";
	}

	public function getRandomRarity() : int{
		$chance = round(lcg_value() * 100, 2);

		if($chance <= 30.5){
			$rarity = CrateData::RARITY_RARE;
		}elseif($chance <= 95.75){
			$rarity = CrateData::RARITY_LEGENDARY;
		}else{
			$rarity = CrateData::RARITY_VOTE;
		}

		return $rarity;
	}

}
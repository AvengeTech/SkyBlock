<?php namespace skyblock\crates\event;

use pocketmine\player\Player;

use skyblock\crates\entity\Crate;
use skyblock\crates\prize\Prize;

class CrateWinEvent extends CratePlayerEvent{
	
	public function __construct(Crate $crate, Player $player, public Prize $prize){
		parent::__construct($crate, $player);
	}

	public function getPrize() : Prize{
		return $this->prize;
	}

}
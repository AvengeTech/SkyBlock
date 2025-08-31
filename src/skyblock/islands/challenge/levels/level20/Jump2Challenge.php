<?php namespace skyblock\islands\challenge\levels\level20;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\Cobblestone;

use skyblock\islands\challenge\Challenge;

class Jump2Challenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$this->progress["jumps"]["progress"]++;
			if($this->progress["jumps"]["progress"] >= 5000){
				$this->onCompleted($player);
			}
			return true;
		}
		return false;
	}

}
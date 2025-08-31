<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level15;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerJumpEvent;
use skyblock\islands\challenge\Challenge;

class Jump1Challenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof PlayerJumpEvent
		) return false;

		$this->progress["jumps"]["progress"]++;

		if($this->progress["jumps"]["progress"] < $this->progress["jumps"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}

}
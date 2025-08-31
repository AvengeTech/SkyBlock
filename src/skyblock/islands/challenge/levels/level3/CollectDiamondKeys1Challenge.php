<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level3;

use skyblock\crates\event\KeyTransactionEvent;
use pocketmine\player\Player;
use skyblock\islands\challenge\Challenge;

class CollectDiamondKeys1Challenge extends Challenge{
	
	public function onKeyEvent(KeyTransactionEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$type = $event->getKeyType();

		if($type !== "diamond") return false;

		$this->progress["collected"]["progress"] += $event->getAmount();

		if($this->progress["collected"]["progress"] < $this->progress["collected"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}
}
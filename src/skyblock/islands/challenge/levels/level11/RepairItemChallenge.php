<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level11;

use pocketmine\player\Player;
use skyblock\enchantments\event\RepairItemEvent;
use skyblock\islands\challenge\Challenge;

class RepairItemChallenge extends Challenge{

	public function onRepairEvent(RepairItemEvent $event, Player $player): bool
	{
		if(!$this->isCompleted()){
			$this->onCompleted($player);
			return true;
		}
		return false;
	}

}
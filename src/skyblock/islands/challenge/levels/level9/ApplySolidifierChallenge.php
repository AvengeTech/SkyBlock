<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level9;

use pocketmine\player\Player;
use skyblock\generators\event\GeneratorApplyItemEvent;
use skyblock\generators\event\GeneratorEvent;
use skyblock\generators\item\Solidifier;
use skyblock\islands\challenge\Challenge;

class ApplySolidifierChallenge extends Challenge{

	public function onGeneratorEvent(GeneratorEvent $event, Player $player) : bool{
		if(
			$this->isCompleted() ||
			!$event instanceof GeneratorApplyItemEvent
		) return false;

		$item = $event->getItem();

		if(!$item instanceof Solidifier) return false;

		$this->onCompleted($player);
		return true;
	}
}
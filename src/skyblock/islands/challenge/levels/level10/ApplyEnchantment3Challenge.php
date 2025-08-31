<?php

declare(strict_types=1);

namespace skyblock\islands\challenge\levels\level10;

use pocketmine\player\Player;
use skyblock\enchantments\event\ApplyEnchantmentEvent;
use skyblock\islands\challenge\Challenge;

class ApplyEnchantment3Challenge extends Challenge{

	public function onApplyEvent(ApplyEnchantmentEvent $event, Player $player) : bool{
		if($this->isCompleted()) return false;

		$this->progress["applied"]["progress"]++;

		if($this->progress["applied"]["progress"] < $this->progress["applied"]["needed"]) return false;

		$this->onCompleted($player);
		return true;
	}
}
<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\items\type\TieredTool;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;

use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\SkyBlock;

class KeyMagnetEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human ||
			!TieredTool::isPickaxe($event->getItem())
		) return false;

		for($i = 1; $i <= $enchantmentLevel; $i++) SkyBlock::getInstance()->getCrates()->excavate($entity, $event->getBlock());

		return true;
	}
}
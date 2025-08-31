<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\items\type\TieredTool;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\Event;
use skyblock\crates\event\KeyFindEvent;
use skyblock\enchantments\type\ReactiveItemEnchantment;

class KeyRushEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof KeyFindEvent) ||
			!$entity instanceof Human ||
			!TieredTool::isPickaxe($entity->getInventory()->getItemInHand())
		) return false;

		$keys = match($enchantmentLevel){
			1 => floor(mt_rand(100, 200) / 100),
			2 => (mt_rand(1, 500) <= 50 ? 2 : (mt_rand(1, 100) === 1 ? 3 : 1)),
			default => 1
		};

		$event->setAmount((int) $keys);
		return true;
	}
}
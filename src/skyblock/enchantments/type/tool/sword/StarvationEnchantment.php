<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use core\AtPlayer;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class StarvationEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof AtPlayer
		) return false;

		if ($victim->getHungerManager()->getFood() > 0) {
			$victim->getHungerManager()->setFood($victim->getHungerManager()->getFood() - 1);
		}

		return true;
	}
}

<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;

class ShuffleEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof SkyBlockPlayer
		) return false;

		switch (mt_rand(0, 2)) {
			case 4:
				$hotbar = [];
				for ($i = 0; $i <= 8; $i++) {
					$hotbar[$i] = $victim->getInventory()->getItem($i);
				}
				shuffle($hotbar);
				$hotbar = array_values($hotbar);
				foreach ($hotbar as $slot => $item) {
					$victim->getInventory()->setItem($slot, $item);
				}
				break;
			default:
				$victim->getInventory()->setHeldItemIndex(mt_rand(0, 8));
				break;
		}

		return true;
	}
}

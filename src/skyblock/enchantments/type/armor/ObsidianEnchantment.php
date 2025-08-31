<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveArmorEnchantment;

class ObsidianEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		$damageReduced = $event->getBaseDamage() * ($enchantmentLevel === 1 ? 0.05 : 0.10);

		$event->setBaseDamage($event->getBaseDamage() - $damageReduced);

		return true;
	}
}

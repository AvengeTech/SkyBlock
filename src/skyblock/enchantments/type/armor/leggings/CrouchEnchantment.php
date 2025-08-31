<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor\leggings;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class CrouchEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		if (!$entity->isSneaking()) return false;

		$event->setBaseDamage($event->getBaseDamage() / (($enchantmentLevel / 2) + 1));

		return true;
	}
}

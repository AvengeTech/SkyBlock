<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\item\Durable;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class PierceEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof Living
		) return false;

		foreach ($victim->getArmorInventory()->getContents(true) as $slot => $armor) {
			if ($armor instanceof Durable) {
				$damage = ($enchantmentLevel * ((int)($armor->getMaxDurability() * 0.0015)));
				$armor->applyDamage($damage);
				$victim->getArmorInventory()->setItem($slot, $armor);
			}
		}
		if (EnchantmentChances::hasChance($this)) $event->setBaseDamage($event->getBaseDamage() + $enchantmentLevel);

		return true;
	}
}

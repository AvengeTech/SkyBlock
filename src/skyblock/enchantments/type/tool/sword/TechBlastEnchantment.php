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
use skyblock\spawners\entity\Mob;

class TechBlastEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof Living
		) return false;

		foreach ($entity->getWorld()->getNearbyEntities($entity->getBoundingBox()->expandedCopy(3, 3, 3)) as $en) {
			if ($en !== $entity && ($en instanceof Mob || $en instanceof AtPlayer)) {
				$dv = $en->getPosition()->subtract($entity->getPosition()->x, $entity->getPosition()->y, $entity->getPosition()->z)->normalize();
				$en->knockback($dv->x, $dv->z);
			}
		}

		return true;
	}
}

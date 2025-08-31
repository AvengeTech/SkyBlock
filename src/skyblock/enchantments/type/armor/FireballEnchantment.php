<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use core\AtPlayer;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;
use skyblock\spawners\entity\Mob;

class FireballEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		foreach ($entity->getWorld()->getNearbyEntities($entity->getBoundingBox()->expandedCopy(3, 3, 3)) as $entity) {
			if ($entity !== $entity && ($entity instanceof Mob || $entity instanceof AtPlayer)) {
				$entity->setOnFire($enchantmentLevel + mt_rand(1, 2));
				if ($entity instanceof SkyBlockPlayer) {
					/** @var SkyBlockPlayer $entity */
					$entity->getGameSession()?->getCombat()->getCombatMode()?->setHit($entity);
				}
			}
		}

		return true;
	}
}

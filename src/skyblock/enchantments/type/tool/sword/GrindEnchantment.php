<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\settings\SkyBlockSettings;
use skyblock\SkyBlockPlayer;
use skyblock\spawners\entity\Mob;

class GrindEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer ||
			!($victim = $event->getEntity()) instanceof Mob
		) return false;

		if ($victim->getHealth() - $event->getFinalDamage() <= 0) {
			$session = $entity->getGameSession()->getSettings();
			if ($session->getSetting(SkyBlockSettings::AUTO_XP)) {
				$entity->getXpManager()->addXp($victim->getXpDropAmount() * $enchantmentLevel);
			} else {
				$victim->getWorld()->dropExperience($victim->getPosition(), $victim->getXpDropAmount() * $enchantmentLevel);
			}
		}

		return true;
	}
}

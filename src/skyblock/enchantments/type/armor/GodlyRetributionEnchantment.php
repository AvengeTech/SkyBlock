<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use core\AtPlayer;
use core\settings\GlobalSettings;
use core\utils\PlaySound;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class GodlyRetributionEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		if ($entity->getHealth() - $event->getBaseDamage() <= 5 && !$entity->getEffects()->has(VanillaEffects::STRENGTH())) {
			if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->addSound($entity->getPosition(), new PlaySound($entity->getPosition(), "mob.wither.ambient"));
			$entity->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 10 * 20));
			$entity->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 10 * 20, 1));
		}

		return true;
	}
}

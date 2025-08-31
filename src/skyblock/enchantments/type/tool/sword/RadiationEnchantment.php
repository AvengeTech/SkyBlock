<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\combat\arenas\entity\MoneyBag;
use skyblock\combat\arenas\entity\SupplyDrop;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class RadiationEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof Living
		) return false;

		if (
			!$victim instanceof MoneyBag &&
			!$victim instanceof SupplyDrop
		) {
			$victim->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 20 * ($enchantmentLevel + (2 * $enchantmentLevel)), 1));
			$victim->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * ($enchantmentLevel + (2 * $enchantmentLevel)), $enchantmentLevel + 1));
		}

		return true;
	}
}

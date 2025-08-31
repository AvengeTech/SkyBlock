<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;

class ElectrifyEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof Living
		) return false;

		EnchantmentUtils::strikeLightning($victim->getPosition());

		$victim->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), ($enchantmentLevel == 1 ? 1 : 2) * 20, $enchantmentLevel == 1 ? 3 : 4));
		return true;
	}
}

<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\world\particle\FlameParticle;
use skyblock\combat\arenas\entity\MoneyBag;
use skyblock\combat\arenas\entity\SupplyDrop;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class HadesEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof Living
		) return false;

		for ($i = 1; $i <= $enchantmentLevel * 3; $i++) {
			$victim->getWorld()->addParticle($victim->getPosition()->add(mt_rand(-10, 10) * 0.1, mt_rand(0, 20) * 0.1, mt_rand(-10, 10) * 0.1), new FlameParticle());
		}

		$victim->setOnFire($enchantmentLevel * mt_rand(1, 2));

		$event->setBaseDamage($event->getBaseDamage() + ($enchantmentLevel * 0.5));

		if (
			$entity instanceof Living &&
			!$entity instanceof MoneyBag &&
			!$entity instanceof SupplyDrop
		) {
			$victim->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * ($enchantmentLevel + (2 * $enchantmentLevel)), 1));
		}

		return true;
	}
}

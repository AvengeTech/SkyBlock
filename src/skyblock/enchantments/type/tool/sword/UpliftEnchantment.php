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

class UpliftEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof Living
		) return false;

		$event->setBaseDamage($event->getBaseDamage() + 1);
		$event->setKnockback($event->getKnockback() * mt_rand(2, 3));
		if (!$victim instanceof AtPlayer) $event->setVerticalKnockBackLimit($event->getVerticalKnockbackLimit() * mt_rand(2, 3));

		return true;
	}
}

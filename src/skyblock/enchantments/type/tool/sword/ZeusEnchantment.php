<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;

class ZeusEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof Living
		) return false;

		EnchantmentUtils::strikeLightning($victim->getPosition());

		$heartCap = 7; // 3.5 hearts

		$event->setModifier(min((($enchantmentLevel / 4) * ($enchantmentLevel + mt_rand(1, 5))), $heartCap), EnchantmentUtils::MODIFIER_ZEUS);

		return true;
	}
}

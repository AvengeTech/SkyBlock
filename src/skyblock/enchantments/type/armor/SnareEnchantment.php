<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use core\AtPlayer;
use core\settings\GlobalSettings;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\world\sound\LaunchSound;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;

class SnareEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		EnchantmentUtils::drag($entity, $damager);
		if ($damager instanceof AtPlayer && $damager->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $damager->getWorld()->addSound($damager->getPosition(), new LaunchSound(), $damager->getViewers());

		return true;
	}
}

<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use core\AtPlayer;
use core\settings\GlobalSettings;
use core\utils\PlaySound;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class DodgeEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		$dx = $entity->getPosition()->x - $damager->getPosition()->x;
		$dz = $entity->getPosition()->z - $damager->getPosition()->z;

		$entity->knockBack($dx, $dz, $event->getKnockBack(), $event->getVerticalKnockBackLimit());

		if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->addSound($entity->getPosition(), new PlaySound($entity->getPosition(), "mob.wither.hurt"));
		$event->cancel();
		return true;
	}

	public function getOrder(): int {
		return 0;
	}
}

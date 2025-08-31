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

class SorceryEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		$bad = [
			VanillaEffects::SLOWNESS(),
			VanillaEffects::MINING_FATIGUE(),
			VanillaEffects::NAUSEA(),
			VanillaEffects::BLINDNESS(),
			VanillaEffects::HUNGER(),
			VanillaEffects::WEAKNESS(),
			VanillaEffects::POISON(),
			VanillaEffects::FATAL_POISON(),
			VanillaEffects::WITHER(),
		];
		$effect = new EffectInstance($bad[array_rand($bad)], 20 * ($enchantmentLevel * 4));
		$damager->getEffects()->add($effect);
		if ($entity instanceof AtPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->addSound($entity->getPosition(), new PlaySound($entity->getPosition(), "mob.evocation_illager.cast_spell"));

		return true;
	}
}

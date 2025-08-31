<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor\chestplate;

use core\settings\GlobalSettings;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;

class RageEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		$entity->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * ($enchantmentLevel * mt_rand(1, 2))));
		$entity->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * ($enchantmentLevel * mt_rand(1, 2))));

		if ($entity instanceof SkyBlockPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), PlaySoundPacket::create(
			"bloom.sculk_catalyst",
			$entity->getPosition()->x,
			$entity->getPosition()->y,
			$entity->getPosition()->z,
			1.0,
			1.0
		));

		return true;
	}
}

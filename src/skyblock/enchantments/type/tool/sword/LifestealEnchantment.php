<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use core\settings\GlobalSettings;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;
use skyblock\SkyBlockPlayer;

class LifestealEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof SkyBlockPlayer
		) return false;

		if ($victim->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $victim->getWorld()->broadcastPacketToViewers($victim->getPosition(), PlaySoundPacket::create(
			"mob.phantom.bite",
			$victim->getPosition()->x,
			$victim->getPosition()->y,
			$victim->getPosition()->z,
			1.0,
			1.0
		));

		$event->setModifier($enchantmentLevel, EnchantmentUtils::MODIFIER_LIFESTEAL);
		$entity->setHealth($entity->getHealth() + ($event->getFinalDamage() / 2));

		return true;
	}
}

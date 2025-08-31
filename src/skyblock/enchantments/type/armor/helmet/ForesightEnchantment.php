<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor\helmet;

use core\settings\GlobalSettings;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;

class ForesightEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!$entity instanceof SkyBlockPlayer ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		$es = $entity->getGameSession()?->getEnchantments();

		if (is_null($es)) return false;

		if ($es->isForeseeing()) {
			$es->addForeseenHits(1);

			if ($es->getHitsForeseen() >= 3) {
				$es->setForeseenHits(0);
				$es->canForesee(false);
			}

			$damageReduced = match ($enchantmentLevel) {
				1 => 0.025,
				2 => 0.05,
				default => 0.10
			};

			$event->setBaseDamage($event->getBaseDamage() - ($event->getBaseDamage() * $damageReduced));
			return true;
		}

		if (!EnchantmentChances::hasChance($this)) return false;

		$es->canForesee(true);

		if ($entity instanceof SkyBlockPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), PlaySoundPacket::create(
			"mob.allay.idle",
			$entity->getPosition()->x,
			$entity->getPosition()->y,
			$entity->getPosition()->z,
			1.0,
			1.0
		));

		return true;
	}
}

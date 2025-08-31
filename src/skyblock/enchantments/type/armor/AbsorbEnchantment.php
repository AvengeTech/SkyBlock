<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use core\settings\GlobalSettings;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\particle\PortalParticle;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class AbsorbEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		$es = $entity->getGameSession()?->getEnchantments();

		if ($es === null) return false;

		if ($es->isAbsorbing()) return false;

		for ($i = 0; $i < 3; $i++) {
			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($entity) {
				if (!($entity->isOnline())) return false;

				if ($entity instanceof SkyBlockPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), PlaySoundPacket::create(
					"random.drink",
					$entity->getPosition()->x,
					$entity->getPosition()->y,
					$entity->getPosition()->z,
					1.0,
					1.0
				));
			}), $i * 5);
		}

		for ($i = 0; $i < 100; $i++) {
			$entity->getWorld()->addParticle($entity->getPosition()->add(mt_rand(-1, 1), mt_rand(-2, 2), mt_rand(-1, 1)), new PortalParticle());
		}

		$es->absorb($entity);

		return true;
	}
}

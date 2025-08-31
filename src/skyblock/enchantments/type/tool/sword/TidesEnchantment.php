<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use core\AtPlayer;
use core\settings\GlobalSettings;
use core\utils\PlaySound;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\world\particle\SplashParticle;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;

class TidesEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof Living
		) return false;

		if ($victim instanceof SkyBlockPlayer && $victim->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $victim->getWorld()->addSound($victim->getPosition(), new PlaySound($victim->getPosition(), "random.splash"));
		for ($i = 0; $i < mt_rand(15, 20); $i++) {
			$victim->getWorld()->addParticle($victim->getPosition()->add(mt_rand(-10, 10) / 10, 0, mt_rand(-10, 10) / 10), new SplashParticle());
		}
		$event->setKnockback($event->getKnockback() * (1 + ($enchantmentLevel / 4)));
		$event->setBaseDamage($event->getBaseDamage() + 1);
		if (!$victim instanceof AtPlayer) $event->setVerticalKnockBackLimit($event->getVerticalKnockBackLimit() * (1 + ($enchantmentLevel / 4)));

		return true;
	}
}

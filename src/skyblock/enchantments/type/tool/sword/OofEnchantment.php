<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use core\Core;
use core\settings\GlobalSettings;
use core\utils\TextFormat;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\scheduler\ClosureTask;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class OofEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer ||
			!($victim = $event->getEntity()) instanceof SkyBlockPlayer
		) return false;

		foreach ($victim->getViewers() as $viewer) {
			/** @var SkyBlockPlayer $viewer */
			if ($viewer instanceof SkyBlockPlayer && $viewer->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $viewer->playSound("random.hurt", $victim->getPosition());
		}
		if ($entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->playSound("random.hurt");

		if (mt_rand(1, 10000) === 1) {
			$pos = $victim->getPosition();
			foreach ($victim->getViewers() as $viewer) {

				/** @var SkyBlockPlayer $viewer */
				for ($i = 0; $i <= 200; $i++) $viewer->playSound("random.hurt", $pos);
				//for($i = 0; $i <= 400; $i++) $viewer->playSound("reverb.fart.long", $victim->getPosition());
				//for($i = 0; $i <= 400; $i++) $viewer->playSound("reverb.fart", $victim->getPosition());

				for ($i = 0; $i <= 20; $i++) {
					SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($viewer, $pos): void {
						if ($viewer->isConnected()) {
							for ($i = 0; $i <= 400; $i++) $viewer->playSound("reverb.fart.long", $pos);
							for ($i = 0; $i <= 20; $i++) $viewer->playSound("reverb.fart", $pos);
						}
					}), $i);
					if ($i % 4 === 0) {
						SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($viewer, $pos): void {
							if ($viewer->isConnected()) {
								for ($i = 0; $i <= 500; $i++) $viewer->playSound("random.hurt", $pos);
							}
						}), $i);
					}
				}
			}
			for ($i = 0; $i <= 500; $i++) $victim->playSound("random.hurt");
			for ($i = 0; $i <= 400; $i++) $victim->playSound("reverb.fart.long");
			for ($i = 0; $i <= 400; $i++) $victim->playSound("reverb.fart");
			$event->setBaseDamage(100000000);
			Core::announceToSS(TextFormat::BOLD . TextFormat::RED . "OOF HAS SMITED.");
		}

		return true;
	}
}

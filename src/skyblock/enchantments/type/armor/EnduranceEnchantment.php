<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use core\settings\GlobalSettings;
use core\utils\GenericSound;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\item\Durable;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;

class EnduranceEnchantment extends ReactiveArmorEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!$entity instanceof SkyBlockPlayer ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		$es = $entity->getGameSession()->getEnchantments();

		if (microtime(true) - $es->getLastHit() < 4.5) {
			$chance = mt_rand(1, 200) <= 10; # 5%
		} elseif (microtime(true) - $es->getLastHit() < 9) {
			$chance = mt_rand(1, 200) <= 5; # 2.5%
		} else {
			$chance = mt_rand(1, 200) <= 2; # 1%
		}

		if (!$chance) return false;

		$damager = $event->getDamager();

		if (!($damager instanceof Living)) return false;

		$vSlot = mt_rand(0, 3);

		/** @var Armor $armor */
		foreach ($entity->getArmorInventory()->getContents(true) as $index => $armor) {
			if ($index !== $vSlot) continue;
			if ($armor->isNull()) continue;
			if (!$armor instanceof Durable || $armor->getDamage() <= 0) continue;

			$damageReduced = $armor->getDamage() - ($enchantmentLevel * ((int)($armor->getMaxDurability() * 0.04)));

			$armor->setDamage(($damageReduced <= 0 ? 0 : $damageReduced));
			$entity->getArmorInventory()->setItem($index, $armor);


			$dSlot = mt_rand(0, 3);

			/** @var Armor $dmgPiece */
			foreach ($damager->getArmorInventory()->getContents(true) as $slot => $dmgPiece) {
				if ($slot !== $dSlot) continue;
				if ($dmgPiece->isNull()) continue;
				if (!$dmgPiece instanceof Durable || $dmgPiece->getDamage() <= 0) continue;

				$dmgPiece->applyDamage(($enchantmentLevel * ((int)($dmgPiece->getMaxDurability() * 0.04))));
				$damager->getArmorInventory()->setItem($slot, $dmgPiece);
				break;
			}

			if ($entity instanceof SkyBlockPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->addSound($entity->getPosition(), new GenericSound($entity->getPosition(), LevelSoundEvent::RANDOM_ANVIL_USE));
			break;
		}

		return true;
	}
}

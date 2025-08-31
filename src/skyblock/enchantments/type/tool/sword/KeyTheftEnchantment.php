<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use core\utils\TextFormat;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;
use skyblock\SkyBlockPlayer;

class KeyTheftEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer ||
			!($victim = $event->getEntity()) instanceof SkyBlockPlayer
		) return false;

		if ($event->getFinalDamage() >= $victim->getHealth()) {
			$stole = [
				"iron" => 0,
				"gold" => 0,
				"diamond" => 0,
				"emerald" => 0,
				"vote" => 0
			];

			for ($i = 1; $i <= $max = mt_rand(1, 3); $i++) {
				$keytype = EnchantmentUtils::getRandomKeyType($victim, $stole);
				if ($keytype !== false) {
					$victim->getGameSession()->getCrates()->takeKeys($keytype, ($amt = mt_rand(1, $enchantmentLevel)));
					$entity->getGameSession()->getCrates()->addKeys($keytype, $amt);
					$stole[$keytype] += $amt;
				}
			}

			$count = 0;
			foreach ($stole as $type => $amount) {
				if ($amount <= 0) {
					unset($stole[$type]);
				} else {
					$count += $amount;
				}
			}

			if ($count > 0) {
				$entity->sendMessage(TextFormat::AQUA . "Stole " . TextFormat::YELLOW . $count . " keys " . TextFormat::AQUA . "from " . TextFormat::RED . $victim->getName() . ":");
				foreach ($stole as $type => $amount) {
					$entity->sendMessage(TextFormat::GRAY . " - " . TextFormat::GREEN . "x" . $amount . " " . $type . " keys");
				}
			}
		}

		return true;
	}
}

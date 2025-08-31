<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\rod;

use pocketmine\entity\Living;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\fishing\event\FishingTugEvent;
use skyblock\SkyBlockPlayer;

class SuperGlueEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof FishingTugEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer
		) return false;

		$tug = $event->getTugTime();
		$tug += $enchantmentLevel * 20;

		$event->setTugTime($tug);
		return true;
	}
}

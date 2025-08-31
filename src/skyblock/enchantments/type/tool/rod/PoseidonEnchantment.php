<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\rod;

use pocketmine\entity\Living;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\fishing\event\FishingPreTugEvent;
use skyblock\SkyBlockPlayer;

class PoseidonEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof FishingPreTugEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer
		) return false;

		$tug = match($enchantmentLevel){
			1 => mt_rand(60, 300),
			2 => mt_rand(50, 170),
			3 => mt_rand(40, 140)
		};

		$event->setNextTug($tug);
		return true;
	}
}

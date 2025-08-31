<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\rod;

use core\utils\TextFormat as TF;
use pocketmine\entity\Living;
use pocketmine\event\Event;
use skyblock\crates\Crates;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\fishing\event\FishingCatchEvent;
use skyblock\SkyBlockPlayer;

class MetalDetectorEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof FishingCatchEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer
		) return false;

		$random = round(lcg_value() * 100, 5);
		$type = match(true){
			($random > 90) => "emerald", // 10%
			($random > 70) => "diamond", // 20%
			($random > 40) => "gold", // 30%
			($random > 0) => "iron", // 40%
			default => "iron"
		};

		$entity->getGameSession()->getCrates()->addKeys($type);
		$entity->sendTitle(TF::YELLOW . Crates::FIND_WORDS[array_rand(Crates::FIND_WORDS)], TF::YELLOW . "Found x1 " . Crates::KEY_COLORS[$type] . ucfirst($type) . " Key", 10, 40, 10);
		return true;
	}
}

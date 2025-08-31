<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\rod;

use pocketmine\entity\Living;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\fishing\event\FishingReelEvent;
use skyblock\fishing\Structure;
use skyblock\SkyBlockPlayer;

class SonarEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof FishingReelEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer
		) return false;

		$chanceAndMulti = match($enchantmentLevel){
			1 => [7.5, 3],
			2 => [15.5, 6],
			3 => [22.5, 8],
			default => [0, 1]
		};

		if(round(lcg_value() * 100, 2) <= $chanceAndMulti[0]){
			$event->setExtraData([
				"categories" => [Structure::CATEGORY_TREASURE],
				"multi" => $chanceAndMulti[1]
			]);
		}
		return true;
	}
}

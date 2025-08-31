<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\rod;

use pocketmine\entity\Living;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\fishing\event\FishingReelEvent;
use skyblock\SkyBlockPlayer;

class FlingEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof FishingReelEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer ||
			(is_null($hook = ($item = $event->getFishingRod())->getHook()) || !$hook->isTouchingLiquid()) ||
			!$entity->isSneaking() ||
			!$entity->isStaff()
		) return false;

		$item->drag($hook, $entity, $enchantmentLevel * 0.8);
		$event->cancel();
		return true;
	}
}

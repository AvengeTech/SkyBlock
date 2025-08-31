<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\items\type\TieredTool;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;

use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class FeatherWeightEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human ||
			!EnchantmentChances::hasChance($this) ||
			!TieredTool::isPickaxe($event->getItem())
		) return false;

		$entity->getEffects()->add(new EffectInstance(
			VanillaEffects::HASTE(), 
			($enchantmentLevel == 4 ? 5 : $enchantmentLevel) * 20 * 3, 
			min(2, $enchantmentLevel - 1)
		));
		return true;
	}
}
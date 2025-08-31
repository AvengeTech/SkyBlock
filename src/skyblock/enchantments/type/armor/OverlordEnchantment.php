<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use pocketmine\entity\Living;

use skyblock\enchantments\type\ToggledArmorEnchantment;

class OverlordEnchantment extends ToggledArmorEnchantment {

	protected function onActivate(Living $entity, int $enchantmentLevel): void {
		$entity->setMaxHealth(20 + ($enchantmentLevel * 2));
	}

	protected function onDeactivate(Living $entity, int $enchantmentLevel): void {
		$entity->setMaxHealth(max(20, $entity->getMaxHealth() - ($enchantmentLevel * 2)));
	}
}

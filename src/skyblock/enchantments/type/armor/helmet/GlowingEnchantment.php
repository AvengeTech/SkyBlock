<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor\helmet;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\utils\Limits;

use skyblock\enchantments\type\ToggledArmorEnchantment;

class GlowingEnchantment extends ToggledArmorEnchantment {

	protected function onActivate(Living $entity, int $enchantmentLevel): void {
		$entity->getEffects()->add(new EffectInstance(
			VanillaEffects::NIGHT_VISION(),
			Limits::INT32_MAX,
			0,
			false
		));
	}

	protected function onDeactivate(Living $entity, int $enchantmentLevel): void {
		$entity->getEffects()->remove(VanillaEffects::NIGHT_VISION());
	}
}

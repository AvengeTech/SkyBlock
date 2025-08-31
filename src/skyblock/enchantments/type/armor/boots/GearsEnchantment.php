<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor\boots;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\utils\Limits;

use skyblock\enchantments\type\ToggledArmorEnchantment;

class GearsEnchantment extends ToggledArmorEnchantment{

	protected function onActivate(Living $entity, int $enchantmentLevel) : void{
		$entity->getEffects()->add(new EffectInstance(
			VanillaEffects::SPEED(), Limits::INT32_MAX,
			$enchantmentLevel - 1, false
		));
	}

	protected function onDeactivate(Living $entity, int $enchantmentLevel) : void{
		$entity->getEffects()->remove(VanillaEffects::SPEED());
	}
}
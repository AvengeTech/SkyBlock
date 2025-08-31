<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\VanillaEnchantments;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;
use skyblock\SkyBlockPlayer;

class ComboEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) || 
			!$entity instanceof Human ||
			!$entity instanceof SkyBlockPlayer ||
			!($victim = $event->getEntity()) instanceof SkyBlockPlayer
		) return false;

		$protection = 0;

		foreach($victim->getArmorInventory() as $slot => $armor){
			if($armor instanceof Armor && $armor->hasEnchantment($prt = VanillaEnchantments::PROTECTION())){
				$lvl = $armor->getEnchantmentLevel($prt);
				$protection += $lvl * 0.045;
			}
		}

		$combo = $entity->getCombo();
		$damage = min(0.75 + $protection, $combo / 15);
		$event->setModifier(($event->getFinalDamage() * (1 + $damage)) - $event->getFinalDamage(), EnchantmentUtils::MODIFIER_COMBO);
		return true;
	}

	public function getOrder() : int{ return 1000; }
}
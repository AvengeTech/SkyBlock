<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\player\Player;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;
use skyblock\SkyBlockPlayer;

class BackstabEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) || 
			!$entity instanceof SkyBlockPlayer ||
			!($victim = $event->getEntity()) instanceof Player
		) return false;

		if(!EnchantmentUtils::isPlayerFacing($entity, $victim)) return false;

		$event->setBaseDamage($event->getBaseDamage() * (1 + (0.1 * $enchantmentLevel)));
		return true;
	}
}
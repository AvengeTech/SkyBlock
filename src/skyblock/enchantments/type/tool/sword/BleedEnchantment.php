<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\world\particle\BlockBreakParticle;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;

class BleedEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) || 
			!$entity instanceof Human ||
			!($victim = $event->getEntity()) instanceof SkyBlockPlayer
		) return false;

		if(round(lcg_value() * 100, 5) <= 25.0) $victim->getGameSession()?->getEnchantments()->blockAbsorb(true);

		$victim->getWorld()->addParticle($victim->getPosition(), new BlockBreakParticle(VanillaBlocks::REDSTONE()));
		$victim->bleed($event->getDamager(), mt_rand(30, 60) * $enchantmentLevel);
		return true;
	}
}
<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\sword;

use core\settings\GlobalSettings;
use core\utils\PlaySound;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\item\Durable;
use pocketmine\world\particle\AngryVillagerParticle;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;
use skyblock\SkyBlockPlayer;

class SpiteEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) ||
			!$entity instanceof SkyBlockPlayer ||
			!($victim = $event->getEntity()) instanceof SkyBlockPlayer
		) return false;

		$heartCap = 9; // 4.5 hearts
		$missingHealth = $entity->getMaxHealth() - $entity->getHealth();

		if ($victim->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $victim->getWorld()->addSound($victim->getPosition(), new PlaySound($victim->getPosition(), 'item.trident.throw', 100));

		for ($i = 0; $i < mt_rand(15, 20); $i++) {
			$victim->getWorld()->addParticle($victim->getPosition()->add(mt_rand(-10, 10) / 10, 0, mt_rand(-10, 10) / 10), new AngryVillagerParticle());
		}

		$itemInHand = $entity->getInventory()->getItemInHand();

		if ($itemInHand instanceof Durable) {
			$itemInHand->applyDamage($enchantmentLevel * (mt_rand(1, 5) !== 1 ? mt_rand(1, 3) : mt_rand(3, 5)));

			$entity->getInventory()->setItemInHand($itemInHand);
		}

		$event->setModifier(min(max($missingHealth, $enchantmentLevel * 2), $heartCap), EnchantmentUtils::MODIFIER_SPITE);

		return true;
	}
}

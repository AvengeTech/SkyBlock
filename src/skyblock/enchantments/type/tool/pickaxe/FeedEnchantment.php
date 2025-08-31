<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\AtPlayer;
use core\items\type\TieredTool;
use core\settings\GlobalSettings;
use core\utils\GenericSound;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class FeedEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human ||
			!EnchantmentChances::hasChance($this) ||
			!TieredTool::isPickaxe($event->getItem())
		) return false;

		$entity->getHungerManager()->setFood(min($entity->getHungerManager()->getFood() + 2, 20));
		$entity->getHungerManager()->setSaturation(min($entity->getHungerManager()->getFood() + 2, 20));

		if(
			$entity instanceof AtPlayer && 
			$entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)
		){
			$entity->getWorld()->addSound($entity->getPosition(), new GenericSound($entity->getPosition(), LevelSoundEvent::BURP));
		}

		return true;
	}
}
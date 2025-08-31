<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\items\type\TieredTool;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class MagnifyEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human ||
			!EnchantmentChances::hasChance($this) ||
			!TieredTool::isPickaxe($event->getItem()) || 
			($xp = $event->getXpDropAmount()) <= 0
		) return false;

		$entity->getXpManager()->addXp($xp * ($enchantmentLevel + 1));
		$entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), LevelEventPacket::create(LevelEvent::SOUND_ORB, 0, $entity->getPosition()));
		return true;
	}
}
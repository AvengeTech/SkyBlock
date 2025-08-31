<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\armor;

use core\settings\GlobalSettings;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;

class BlessingEnchantment extends ReactiveArmorEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof EntityDamageByEntityEvent) ||
			!EnchantmentChances::hasChance($this) || 
			!$entity instanceof Human ||
			!($damager = $event->getDamager()) instanceof Living
		) return false;

		$bad = [
			EffectIdMap::getInstance()->toId(VanillaEffects::SLOWNESS()),
			EffectIdMap::getInstance()->toId(VanillaEffects::MINING_FATIGUE()),
			EffectIdMap::getInstance()->toId(VanillaEffects::NAUSEA()),
			EffectIdMap::getInstance()->toId(VanillaEffects::BLINDNESS()),
			EffectIdMap::getInstance()->toId(VanillaEffects::HUNGER()),
			EffectIdMap::getInstance()->toId(VanillaEffects::WEAKNESS()),
			EffectIdMap::getInstance()->toId(VanillaEffects::POISON()),
			EffectIdMap::getInstance()->toId(VanillaEffects::FATAL_POISON()),
			EffectIdMap::getInstance()->toId(VanillaEffects::WITHER()),
		];

		foreach($damager->getEffects()->all() as $effect){
			if(in_array(EffectIdMap::getInstance()->toId($effect->getType()), $bad)){
				$entity->getEffects()->remove($effect->getType());
				$damager->getEffects()->add($effect);
			}
		}

		if ($entity instanceof SkyBlockPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), PlaySoundPacket::create(
			"beacon.power",
			$entity->getPosition()->x,
			$entity->getPosition()->y,
			$entity->getPosition()->z,
			1.0,
			1.0
		));

		return true;
	}
}
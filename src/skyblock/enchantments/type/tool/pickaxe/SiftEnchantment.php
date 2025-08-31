<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\items\type\TieredTool;
use pocketmine\block\BlockTypeIds;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\ItemTypeIds;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class SiftEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human ||
			!TieredTool::isPickaxe($event->getItem())
		) return false;

		$multipliers = [
			ItemTypeIds::COAL,
			ItemTypeIds::IRON_INGOT,
			ItemTypeIds::IRON_NUGGET,
			ItemTypeIds::GOLD_INGOT,
			ItemTypeIds::GOLD_NUGGET,
			-BlockTypeIds::IRON_ORE,
			-BlockTypeIds::GOLD_ORE,
			ItemTypeIds::NETHER_QUARTZ,
			ItemTypeIds::REDSTONE_DUST,
			ItemTypeIds::DIAMOND,
			ItemTypeIds::EMERALD
		];
		$drops = $event->getDrops();

		foreach($drops as $key => $drop){
			if(
				!in_array($drop->getTypeId(), $multipliers) ||
				!EnchantmentChances::hasChance($this)
			) continue;

			$drops[$key] = $drop->setCount($drop->getCount() * mt_rand(2, $enchantmentLevel + 2));
		}

		$event->setDrops($drops);
		return true;
	}
}
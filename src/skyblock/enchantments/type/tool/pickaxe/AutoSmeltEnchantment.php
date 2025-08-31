<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\items\type\TieredTool;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class AutoSmeltEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human ||
			!EnchantmentChances::hasChance($this) ||
			!TieredTool::isPickaxe($event->getItem())
		) return false;

		$convert = [
			ItemTypeIds::RAW_IRON => VanillaItems::IRON_INGOT(),
			ItemTypeIds::RAW_GOLD => VanillaItems::GOLD_INGOT(),
			ItemTypeIds::RAW_COPPER => VanillaItems::COPPER_INGOT()
		];
		$drops = $event->getDrops();

		foreach($drops as $key => $drop){
			if(!isset($convert[$drop->getTypeId()])) continue;

			$drops[$key] = $convert[$drop->getTypeId()]->setCount($drop->getCount());
		}

		$event->setDrops($drops);
		return true;
	}
}
<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\items\type\TieredTool;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;

class TransfusionEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human ||
			!EnchantmentChances::hasChance($this) ||
			!TieredTool::isPickaxe($event->getItem())
		) return false;

		$convert = [
			ItemTypeIds::COAL => VanillaItems::IRON_INGOT(),
			ItemTypeIds::IRON_INGOT => VanillaItems::GOLD_INGOT(),
			ItemTypeIds::GOLD_INGOT => VanillaItems::REDSTONE_DUST(),
			ItemTypeIds::REDSTONE_DUST => VanillaItems::DIAMOND(),
			ItemTypeIds::DIAMOND => VanillaItems::EMERALD(),
			ItemTypeIds::EMERALD => VanillaBlocks::OBSIDIAN()->asItem(),
		];

		$converted = false;
		$drops = $event->getDrops();

		foreach($drops as $key => $drop){
			$id = $drop->getTypeId();

			if(!isset($convert[$id])) continue;

			$convertedItem = $convert[$id];
			$drops[$key] = $convertedItem->setCount($drop->getCount());
			$converted = true;
		}

		if($converted) $event->setDrops($drops);

		return true;
	}
}
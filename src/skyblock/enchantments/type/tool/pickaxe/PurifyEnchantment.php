<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\pickaxe;

use core\items\type\TieredTool;
use core\settings\GlobalSettings;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class PurifyEnchantment extends ReactiveItemEnchantment{

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool{
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!($entity instanceof SkyBlockPlayer && $entity->isLoaded()) ||
			!EnchantmentChances::hasChance($this) ||
			!TieredTool::isPickaxe($event->getItem())
		) return false;

		$multipliers = [
			BlockTypeIds::COBBLESTONE,
			ItemTypeIds::COAL,
			ItemTypeIds::IRON_INGOT,
			ItemTypeIds::GOLD_INGOT,
			BlockTypeIds::IRON_ORE,
			BlockTypeIds::GOLD_ORE,
			ItemTypeIds::NETHER_QUARTZ,
			ItemTypeIds::DIAMOND,
			ItemTypeIds::EMERALD
		];

		$islandLevel = $entity->getGameSession()->getIslands()->getIslandAt()?->getSizeLevel() ?? 1;
		$drops = $event->getDrops();
		$price = 0;

		foreach($drops as $key => $drop){
			if(
				!in_array($drop->getTypeId(), $multipliers) || 
				($value = SkyBlock::getInstance()->getShops()->getValue($drop, $islandLevel)) == -1
			) continue;

			$price += $value;
			unset($drops[$key]);
		}

		if($price < 1) return false;

		$event->setDrops($drops);
		$entity->addTechits((int) ($price * (1 + (0.25 * $enchantmentLevel))));

		if($entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->playSound("break.amethyst_block", $entity->getPosition()->subtract(0, 5, 0), 50, 1);

		return true;
	}
}
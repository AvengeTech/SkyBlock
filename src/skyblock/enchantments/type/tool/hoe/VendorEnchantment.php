<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\hoe;

use core\items\type\TieredTool;
use pocketmine\block\Bamboo;
use pocketmine\block\Cactus;
use pocketmine\block\ChorusPlant;
use pocketmine\block\Crops;
use pocketmine\block\Melon;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\Pumpkin;
use pocketmine\block\Sugarcane;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\Hoe;
use skyblock\block\RedMushroomBlock;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class VendorEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof SkyBlockPlayer ||
			!$event->getItem() instanceof Hoe
		) return false;

		if (!$entity->isSneaking()) return false;

		$isession = $entity->getGameSession()->getIslands();
		$island = $isession->getIslandAt() ?? $isession->getLastIslandAt();

		if (is_null($island)) return false;

		$block = $event->getBlock();

		if (!(
			$block instanceof Crops || $block instanceof Bamboo ||
			$block instanceof Melon || $block instanceof Pumpkin ||
			$block instanceof Sugarcane || $block instanceof Cactus ||
			$block instanceof RedMushroomBlock || $block instanceof NetherWartPlant ||
			$block instanceof ChorusPlant
		)) return false;

		$price = 0;

		$drops = [];

		foreach ($block->getDrops($event->getItem()) as $drop) {
			$value = SkyBlock::getInstance()->getShops()->getValue($drop, $island->getSizeLevel(), $entity);

			if ($value > -1) {
				$price += $value;
			} else {
				$drops[] = $drop;
			}
		}

		if ($price > 0) $entity->addTechits((int)$price);

		$event->setDrops($drops);

		return true;
	}
}

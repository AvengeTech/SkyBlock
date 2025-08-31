<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\axe;

use core\items\type\TieredTool;
use core\settings\GlobalSettings;
use core\utils\PlaySound;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wood;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\SkyBlockPlayer;

class LumberjackEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human
		) return false;

		if (!$entity->isSneaking()) return false;

		$block = $event->getBlock();
		$drops = [];

		if ($block instanceof Wood) {
			$count = 0;
			while ($block instanceof Wood && $count <= 10) {
				$count++;
				$entity->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());
				$drops[] = $block->asItem();
				$pos = $block->getPosition();
				$block = $entity->getWorld()->getBlockAt($pos->getX(), $pos->getY() + 1, $pos->getZ());
			}

			if ($entity instanceof SkyBlockPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->addSound($entity->getPosition(), new PlaySound($entity->getPosition(), "random.pop"));
			$event->setDrops($drops);
		}

		return true;
	}
}

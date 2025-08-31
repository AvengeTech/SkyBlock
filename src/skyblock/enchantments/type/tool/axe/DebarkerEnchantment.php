<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\axe;

use core\items\type\TieredTool;
use core\settings\GlobalSettings;
use core\utils\PlaySound;
use pocketmine\block\Wood;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\world\particle\BlockBreakParticle;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\SkyBlockPlayer;

class DebarkerEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof PlayerInteractEvent) ||
			!$entity instanceof Human
		) return false;

		if (!$entity->isSneaking()) return false;
		if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return false;

		$block = $event->getBlock();

		if ($block instanceof Wood) {
			$count = 0;
			while ($block instanceof Wood && $count <= 10) {
				$pos = $block->getPosition();

				$count++;

				$pos->getWorld()->addParticle($pos, new BlockBreakParticle($block));
				$pos->getWorld()->setBlock($pos, $block->setStripped(true));

				$block = $entity->getWorld()->getBlockAt($pos->getX(), $pos->getY() + 1, $pos->getZ());
			}

			if ($entity instanceof SkyBlockPlayer && $entity->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $entity->getWorld()->addSound($entity->getPosition(), new PlaySound($entity->getPosition(), "random.pop"));
		}

		return true;
	}
}

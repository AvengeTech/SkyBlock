<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\axe;

use core\items\type\Axe;
use pocketmine\block\Crops;
use pocketmine\block\Melon;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\Pumpkin;
use pocketmine\block\Stem;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\sound\BlockBreakSound;
use skyblock\enchantments\type\ReactiveItemEnchantment;

class WormEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if(
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof Human ||
			!$event->getItem() instanceof Axe
		) return false;

		$item = $event->getItem();
		$block = $event->getBlock();

		if(!(
			$block instanceof Crops ||
			$block instanceof Melon ||
			$block instanceof Pumpkin ||
			$block instanceof NetherWartPlant
		)) return false;

		$face = $entity->getHorizontalFacing();
		$drops = $event->getDrops();

		for($i = 1; $i < $enchantmentLevel; $i++){
			$pos = $block->getPosition()->getSide($face, $i);
			$found = $pos->getWorld()->getBlock($pos);

			if(!(
				$found instanceof Crops && !$found instanceof Stem ||
				$found instanceof Melon ||
				$found instanceof Pumpkin ||
				$found instanceof NetherWartPlant
			)) break;

			foreach ($found->getDrops($item) as $drop) $drops[] = $drop;

			$pos->getWorld()->setBlock($pos, VanillaBlocks::AIR());
			$pos->getWorld()->addParticle($pos, new BlockBreakParticle($block));
			$pos->getWorld()->addSound($pos, new BlockBreakSound($block));
		}

		$event->setDrops($drops);

		return true;
	}
}

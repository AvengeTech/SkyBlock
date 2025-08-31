<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\hoe;

use core\AtPlayer;
use core\items\type\TieredTool;
use pocketmine\block\Dirt;
use pocketmine\block\Farmland;
use pocketmine\block\Grass;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Living;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Hoe;
use pocketmine\math\AxisAlignedBB;
use pocketmine\world\particle\BlockBreakParticle;
use skyblock\enchantments\type\ReactiveItemEnchantment;

class TillerEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof PlayerInteractEvent) ||
			!$entity instanceof AtPlayer ||
			!$event->getItem() instanceof Hoe
		) return false;

		if (!$entity->isSneaking() || $event->getAction() != PlayerInteractEvent::RIGHT_CLICK_BLOCK) return false;

		$block = $event->getBlock();

		if (!(
			$block instanceof Dirt ||
			$block instanceof Grass
		)) return false;

		$pos = $block->getPosition();
		$bb = new AxisAlignedBB(
			$pos->x,
			$pos->y,
			$pos->z,
			$pos->x,
			$pos->y,
			$pos->z
		);
		$bb->expand($enchantmentLevel, 0, $enchantmentLevel);

		for ($x = $bb->minX; $x <= $bb->maxX; $x++) {
			for ($z = $bb->minZ; $z <= $bb->maxZ; $z++) {
				$ground = $pos->getWorld()->getBlockAt((int)round($x), (int)round($pos->y), (int)round($z));

				if (!(
					$ground instanceof Dirt ||
					$ground instanceof Grass
				)) continue;

				$pos->getWorld()->setBlock($ground->getPosition(), VanillaBlocks::FARMLAND()->setWetness(($enchantmentLevel === 4 ? Farmland::MAX_WETNESS : $enchantmentLevel - 1)));
				$pos->getWorld()->addParticle($ground->getPosition()->add(mt_rand(-20, 20) / 20, (mt_rand(-20, 20) / 20) + 1, mt_rand(-20, 20) / 20), new BlockBreakParticle($ground));
			}
		}

		return true;
	}
}

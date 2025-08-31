<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\hoe;

use core\items\type\TieredTool;
use pocketmine\block\Crops;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\Hoe;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\sound\BlockBreakSound;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\SkyBlockPlayer;

class HarvestEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof SkyBlockPlayer ||
			!$event->getItem() instanceof Hoe
		) return false;

		$item = $event->getItem();
		$block = $event->getBlock();

		if (!(
			$block instanceof Crops ||
			$block instanceof NetherWartPlant
		)) return false;

		if ($enchantmentLevel == 2) {
			$positions = [
				[1, 1],
				[0, 1],
				[1, 0],
				[-1, -1,],
				[0, -1],
				[-1, 0],
				[0, 0],
				[-1, 1],
				[1, -1]
			];
		} else {
			$positions = [
				[1, 0],
				[0, 1],
				[0, 0],
				[1, 1]
			];
		}

		$drops = [];

		foreach ($positions as [$x, $z]) {
			$found = $entity->getWorld()->getBlock($block->getPosition()->add($x, 0, $z));

			if (!($found instanceof Crops || $found instanceof NetherWartPlant)) continue;

			foreach ($found->getDrops($item) as $drop) {
				$drops[] = $drop;
			}

			$entity->getWorld()->setBlock($found->getPosition(), VanillaBlocks::AIR());
			$entity->getWorld()->addParticle($found->getPosition(), new BlockBreakParticle($found));
			$entity->getWorld()->addSound($found->getPosition(), new BlockBreakSound($found));

			if (
				!is_null(($ench = $item->getEnchantment(EnchantmentRegistry::CAPSULE()->getEnchantment()))) &&
				$block->getPosition()->distance($found->getPosition()) >= 1
			) {
				$react = $ench->getType();
				if ($react instanceof CapsuleEnchantment) {
					$react->react($entity, $ench->getLevel(), $ev = new BlockBreakEvent(
						$entity,
						$found,
						$item,
						$event->getInstaBreak(),
						$drops,
						$found->getXpDropForTool($item)
					));
					$drops = $ev->getDrops();
				}
			}

			if (
				!is_null(($ench = $item->getEnchantment(EnchantmentRegistry::BURROW()->getEnchantment()))) &&
				$block->getPosition()->distance($found->getPosition()) >= 1
			) {
				$react = $ench->getType();
				if ($react instanceof BurrowEnchantment) {
					$react->react($entity, $ench->getLevel(), $ev = new BlockBreakEvent(
						$entity,
						$found,
						$item,
						$event->getInstaBreak(),
						$drops,
						$found->getXpDropForTool($item)
					));
					$drops = $ev->getDrops();
				}
			}

			if (
				!is_null(($ench = $item->getEnchantment(EnchantmentRegistry::FERTILIZE()->getEnchantment()))) &&
				$block->getPosition()->distance($found->getPosition()) >= 1
			) {
				$react = $ench->getType();
				if ($react instanceof FertilizeEnchantment) {
					$dropReturn = [];
					$react->react($entity, $ench->getLevel(), $ev = new BlockBreakEvent(
						$entity,
						$found,
						$item,
						$event->getInstaBreak(),
						$drops,
						$found->getXpDropForTool($item)
					), $dropReturn);
					$drops = $dropReturn;
				}
			}

			if (
				!is_null(($ench = $item->getEnchantment(EnchantmentRegistry::VENDOR()->getEnchantment()))) &&
				$block->getPosition()->distance($found->getPosition()) >= 1
			) {
				$react = $ench->getType();
				if ($react instanceof VendorEnchantment) {
					$react->react($entity, $ench->getLevel(), $ev = new BlockBreakEvent(
						$entity,
						$found,
						$item,
						$event->getInstaBreak(),
						$drops,
						$found->getXpDropForTool($item)
					));
					$drops = $ev->getDrops();
				}
			}
		}

		$event->setDrops($drops);

		return true;
	}

	public function getOrder(): int
	{
		return -2;
	}
}

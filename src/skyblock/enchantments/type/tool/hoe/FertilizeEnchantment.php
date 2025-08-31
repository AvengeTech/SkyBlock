<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\hoe;

use core\items\type\TieredTool;
use pocketmine\block\Crops;
use pocketmine\block\MelonStem;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\PumpkinStem;
use pocketmine\block\Wheat;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\Hoe;
use pocketmine\item\VanillaItems;
use pocketmine\item\WheatSeeds;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\particle\HappyVillagerParticle;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class FertilizeEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof SkyBlockPlayer ||
			!$event->getItem() instanceof Hoe
		) return false;

		$block = $event->getBlock();

		if (!(
			$block instanceof Crops ||
			$block instanceof NetherWartPlant
		)) return false;

		$seed = match (true) {
			$block instanceof Wheat => VanillaItems::WHEAT_SEEDS(),
			$block instanceof MelonStem => VanillaItems::MELON_SEEDS(),
			$block instanceof PumpkinStem => VanillaItems::PUMPKIN_SEEDS(),
			default => $block->asItem()
		};

		$fromDrop = false;
		$extraData = $event->getDrops();

		if (!$seed instanceof WheatSeeds) {
			foreach ($extraData as $k => $d) {
				if ($d->getTypeId() == $seed->getTypeId()) {
					$d->setCount($d->getCount() - 1);
					if ($d->getCount() <= 0) {
						unset($extraData[$k]);
					} else {
						$extraData[$k] = $d;
					}
					$fromDrop = true;
					$event->setDrops($extraData);
					break;
				}
			}
		}

		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($entity, $block, $enchantmentLevel, $seed, $fromDrop) {
			if (!$entity->isOnline() || !$entity->isAlive() || $entity->isFlaggedForDespawn()) return;

			if (($first = $entity->getInventory()->first($seed)) == -1 && !$fromDrop) return false;

			if (!$fromDrop) {
				$seed = $entity->getInventory()->getItem($first);
				$entity->getInventory()->setItem($first, $seed->setCount($seed->getCount() - 1));
			}

			if (
				$block->getAge() === Crops::MAX_AGE ||
				$block->getAge() === NetherWartPlant::MAX_AGE
			) {
				$maxAge = ($block instanceof NetherWartPlant ? NetherWartPlant::MAX_AGE : Crops::MAX_AGE);

				$ageChance = mt_rand(1, 100);
				if ($block instanceof NetherWartPlant) {
					$age = match ($enchantmentLevel) {
						1 => ($ageChance <= 80 ? 0 : ($ageChance <= 95 ? 1 : 2)),
						2 => ($ageChance <= 60 ? 0 : ($ageChance <= 80 ? 1 : 2)),
						3 => ($ageChance <= 30 ? 0 : ($ageChance <= 60 ? mt_rand(1, 2) : $maxAge)),
						default => 0
					};
				} else {
					$age = match ($enchantmentLevel) {
						1 => ($ageChance <= 80 ? 0 : ($ageChance <= 95 ? mt_rand(1, 2) : mt_rand(3, $maxAge))),
						2 => ($ageChance <= 60 ? 0 : ($ageChance <= 80 ? mt_rand(1, 3) : mt_rand(4, $maxAge))),
						3 => ($ageChance <= 30 ? 0 : ($ageChance <= 60 ? mt_rand(1, 4) : mt_rand(5, $maxAge))),
						default => 0
					};
				}

				$block->setAge($age);
			} else {
				$block->setAge(0);
			}
			$entity->getWorld()->setBlock($block->getPosition(), $block);

			for ($i = 0; $i < mt_rand(25, 35); $i++) {
				$sound = PlaySoundPacket::create(
					"random.bow",
					$block->getPosition()->x,
					$block->getPosition()->y,
					$block->getPosition()->z,
					0.50,
					1.0
				);
				$entity->getWorld()->broadcastPacketToViewers($entity->getPosition(), $sound);
				$entity->getWorld()->addParticle($block->getPosition()->add(mt_rand(-20, 20) / 20, mt_rand(-20, 20) / 20, mt_rand(-20, 20) / 20), new HappyVillagerParticle());
			}
		}), 20);

		return true;
	}

	public function getOrder(): int {
		return 0;
	}
}

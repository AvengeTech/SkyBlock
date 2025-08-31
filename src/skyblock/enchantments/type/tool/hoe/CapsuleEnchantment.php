<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\hoe;

use core\items\type\TieredTool;
use core\utils\PlaySound;
use pocketmine\block\Bamboo;
use pocketmine\block\Cactus;
use pocketmine\block\ChorusPlant;
use pocketmine\block\Crops;
use pocketmine\block\Melon;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\Pumpkin;
use pocketmine\block\Sugarcane;
use pocketmine\color\Color;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\item\Hoe;
use pocketmine\world\particle\DustParticle;
use skyblock\block\RedMushroomBlock;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\enchantments\utils\EnchantmentUtils;
use skyblock\SkyBlockPlayer;
use skyblock\techits\item\TechitNote;

class CapsuleEnchantment extends ReactiveItemEnchantment {

	public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []): bool {
		if (
			(is_null($event) || !$event instanceof BlockBreakEvent) ||
			!$entity instanceof SkyBlockPlayer ||
			!EnchantmentChances::hasChance($this) ||
			!$event->getItem() instanceof Hoe
		) return false;

		$block = $event->getBlock();

		if (!(
			$block instanceof Crops || $block instanceof Bamboo ||
			$block instanceof Melon || $block instanceof Pumpkin ||
			$block instanceof Sugarcane || $block instanceof Cactus ||
			$block instanceof RedMushroomBlock || $block instanceof NetherWartPlant ||
			$block instanceof ChorusPlant
		)) return false;

		if (
			$block instanceof Sugarcane ||
			$block instanceof Crops ||
			$block instanceof NetherWartPlant
		) {
			if (!EnchantmentChances::hasChance($this, 23)) return false;
		}

		$reward = EnchantmentUtils::getRandomCapsuleItem($enchantmentLevel);
		$giveItem = true;

		if ($reward instanceof TechitNote) {
			// Can't do getInventory()->first() & get item from slot because all techit notes have different ids
			foreach ($entity->getInventory()->getContents(true) as $slot => $item) {
				if (!$item instanceof TechitNote) continue;
				if (!$item->getCreatedBy() === "CAPSULE" . EnchantmentUtils::getRoman($enchantmentLevel)) continue;

				$item->setup("CAPSULE" . EnchantmentUtils::getRoman($enchantmentLevel), $item->getTechits() + (1500 * $enchantmentLevel));
				$entity->getInventory()->setItem($slot, $item);
				$giveItem = false;
				break;
			}
		}

		if ($giveItem) {
			$entity->getInventory()->addItem($reward);
		}

		for ($i = 0; $i < mt_rand(25, 30); $i++) {
			$entity->getWorld()->addSound($entity->getPosition(), new PlaySound($entity->getPosition(), "block.chain.place"));
			$entity->getWorld()->addParticle($block->getPosition()->add(mt_rand(-20, 20) / 20, mt_rand(-20, 20) / 20, mt_rand(-20, 20) / 20), new DustParticle(new Color(242, 183, 90, 1)));
		}

		return true;
	}
}

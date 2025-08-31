<?php

declare(strict_types=1);

namespace skyblock\enchantments\type\tool\hoe;

use core\items\type\TieredTool;
use core\utils\TextFormat;
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
use skyblock\crates\Crates;
use skyblock\crates\event\KeyFindEvent;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\enchantments\utils\EnchantmentChances;
use skyblock\SkyBlockPlayer;

class BurrowEnchantment extends ReactiveItemEnchantment {

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
			if (!EnchantmentChances::hasChance($this, 8)) return false; // kinda maintains the original chances, just very slightly higher chances ex.(0.75 -> 0.76)
		}

		$type = null;
		$ktr = round(lcg_value() * 100, 3);

		switch (true) {
			case $ktr <= 0.0025:
				$type = "divine";
				break;
			case $ktr <= 4.25:
				$type = "emerald";
				break;
			case $ktr <= 9.5:
				$type = "diamond";
				break;
			case $ktr <= 45.5:
				$type = "gold";
				break;
			case $ktr <= 75.5:
				$type = "iron";
				break;
			default:
				return false;
		}

		$event = new KeyFindEvent($entity, $type, 1);
		$event->call();

		if ($event->isCancelled()) return false;

		$entity->getGameSession()->getCrates()->addKeys($type, $event->getAmount());
		$entity->playSound("mob.chicken.hurt");
		$entity->sendTitle(
			TextFormat::YELLOW . Crates::FIND_WORDS[array_rand(Crates::FIND_WORDS)],
			TextFormat::YELLOW . "Found x" . $event->getAmount() . " " . Crates::KEY_COLORS[$type] . ucfirst($type) . " Key",
			10,
			40,
			10
		);

		return true;
	}
}

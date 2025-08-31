<?php

declare(strict_types=1);

namespace skyblock\enchantments\utils;

use core\AtPlayer;
use core\settings\GlobalSettings;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use core\utils\PlaySound;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\Position;

use skyblock\enchantments\EnchantmentData as ED;
use skyblock\pets\block\PetBox;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\settings\SkyBlockSettings;

class EnchantmentUtils {

	const MODIFIER_KABOOM = 50;
	const MODIFIER_SPITE = 51;
	const MODIFIER_LIFESTEAL = 51;
	const MODIFIER_ZEUS = 52;
	const MODIFIER_COMBO = 53;
	const MODIFIER_DODGE = 55;

	const MODIFIER_DAMAGE_CORRECTION = 54;

	public static function getRandomChance(): float {
		return round(lcg_value() * 100, 5);
	}

	public static function getRoman(int $number): string {
		$result = "";
		$roman_numerals = [
			"M" => 1000,
			"CM" => 900,
			"D" => 500,
			"CD" => 400,
			"C" => 100,
			"XC" => 90,
			"L" => 50,
			"XL" => 40,
			"X" => 10,
			"IX" => 9,
			"V" => 5,
			"IV" => 4,
			"I" => 1
		];
		foreach ($roman_numerals as $roman => $num) {
			$matches = intval($number / $num);
			$result .= str_repeat($roman, $matches);
			$number = $number % $num;
		}
		return $result;
	}

	public static function isPlayerFacing(Player $player1, Player $player2, float $tolerance = 0.35): bool {
		$direction1 = $player1->getDirectionVector();
		$direction2 = $player2->getDirectionVector();

		// Normalize the direction vectors
		$direction1->normalize();
		$direction2->normalize();

		// Calculate the dot product of the direction vectors
		$dotProduct = $direction1->dot($direction2);

		// Check if the dot product is within the tolerance range
		return $dotProduct >= -1 - $tolerance && $dotProduct <= -1 + $tolerance;
	}

	public static function explosion(Position $pos, int $size = 2) {
		$pos->getWorld()->addParticle($pos, new HugeExplodeParticle());
		foreach ($pos->getWorld()->getPlayers() as $player) {
			if ($player instanceof AtPlayer && $player->getSession()?->getSettings()->getSetting(GlobalSettings::ENCHANTMENT_SOUNDS)) $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::create(LevelSoundEvent::EXPLODE, $pos, -1, ":", false, false, -1));
		}
	}

	public static function strikeLightning(Position $pos): void {
		$pos->getWorld()->addSound($pos, new PlaySound($pos, "ambient.weather.lightning.impact"));
		$pk = new AddActorPacket();
		$pk->type = "minecraft:lightning_bolt";
		$pk->actorRuntimeId = $pk->actorUniqueId = $eid = Entity::nextRuntimeId();
		$pk->position = $pos->asVector3();
		$pk->yaw = $pk->pitch = 0;
		$pk->syncedProperties = new PropertySyncData([], []);

		$p2d = [];
		foreach ($pos->getWorld()->getPlayers() as $p) {
			/** @var SkyBlockPlayer $p */
			if ($p->isLoaded() && $p->getGameSession()->getSettings()->getSetting(SkyBlockSettings::LIGHTNING)) {
				$p->getNetworkSession()->sendDataPacket($pk);
				$p2d[] = $p;
			}
		}
		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($p2d, $eid): void {
			$pk = new RemoveActorPacket();
			$pk->actorUniqueId = $eid;
			foreach ($p2d as $p) if ($p->isConnected()) $p->getNetworkSession()->sendDataPacket($pk);
		}), 20);
	}

	public static function getRandomKeyType(Player $player, array $takingalready = [], int $tries = 0) {
		/** @var SkyBlockPlayer $player */
		if ($tries >= 10) return false;
		$type = ["iron", "gold", "diamond", "emerald", "vote"][(
			0 + 									// iron
			(1 * floor(mt_rand(80, 140) / 100)) +	// gold
			(1 * floor(mt_rand(50, 130) / 100)) +	// diamond
			(1 * floor(mt_rand(15, 120) / 100)) +	// emerald
			(1 * floor(mt_rand(0, 110) / 100))		// vote
		)];
		if (mt_rand(1, 100) <= 40) $type = "iron";
		$amt = $player->getGameSession()->getCrates()->getKeys($type);
		if (($amt - $takingalready[$type]) <= 0 && $tries < 10) {
			$tries++;
			$type = self::getRandomKeyType($player, $takingalready, $tries);
		}
		return $type;
	}

	public static function getRandomCapsuleItem(int $level = 1): Item {
		$items = [
			VanillaItems::GOLDEN_APPLE()->setCount(mt_rand(1, $level == 1 ? mt_rand(2, 3) : mt_rand(2, 6))),
			ItemRegistry::EXPERIENCE_BOTTLE()->setCount(mt_rand(1, $level == 1 ? mt_rand(3, 8) : mt_rand(3, 16))),
		];
		$rare = [
			ItemRegistry::NAMETAG()->init(),
			ItemRegistry::CUSTOM_DEATH_TAG()->init(),
			ItemRegistry::TECHIT_NOTE()->setup("CAPSULE" . EnchantmentUtils::getRoman($level), 250 * $level),
			ItemRegistry::GEN_BOOSTER()->setup((50 * $level)),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(ED::RARITY_COMMON)->init(),
			ItemRegistry::SELL_WAND()->init()
		];
		$very_rare = [
			ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount(mt_rand(1, $level == 1 ? 1 : mt_rand(2, 3))),
			ItemRegistry::PET_KEY()->init(),
			BlockRegistry::PET_BOX()->addData(BlockRegistry::PET_BOX()->asItem()),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(ED::RARITY_UNCOMMON)->init(),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(ED::RARITY_RARE)->init(),
			ItemRegistry::ESSENCE_OF_ASCENSION()->setup(ED::RARITY_LEGENDARY)->init()
		];

		if ($level >= 2) {
			if (mt_rand(1, 100) <= 35) {
				foreach ($rare as $i) $items[] = $i;
			}

			if ($level >= 3) {
				if (round(lcg_value() * 100, 5) <= 0.00985) {
					foreach ($very_rare as $i) {
						if (!(
							$i instanceof ItemBlock &&
							$i->getBlock() instanceof PetBox &&
							mt_rand(1, 100) <= 45
						)) continue;

						$items[] = $i;
					}
				}
			}
		}

		return $items[array_rand($items)];
	}

	public static function drag(Player $to, Entity $from): void {
		if (!$from instanceof Living) return;
		$t = $from->getPosition()->asVector3();
		$dv = $to->getPosition()->asVector3()->subtract($t->x, $t->y, $t->z)->normalize();
		$from->knockBack($dv->x * 1.5, $dv->z * 1.5, 0.5, 0.15);
	}
}

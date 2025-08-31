<?php

declare(strict_types=1);

namespace skyblock\enchantments\type;

use core\items\type\TieredTool;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerInteractEvent;
use skyblock\crates\event\KeyFindEvent;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\utils\EnchantmentUtils;
use skyblock\fishing\event\FishingEvent;

abstract class ReactiveItemEnchantment extends Enchantment{

	/**
	 * 
	 * @param Living $entity			Entity with the item
	 * @param int $enchantmentLevel		Enchantment Stored Level
	 * @param ?Event $event				Event called
	 * @param array $extraData			Extra Data
	 *
	 * @return bool
	 */
	abstract public function react(Living $entity, int $enchantmentLevel, ?Event $event = null, array &$extraData = []) : bool;

	final public static function onReact(Event $event) : void{
		$closure = match(true){
			$event instanceof EntityDamageByEntityEvent => function(EntityDamageByEntityEvent $ev) : void{
				$damager = $ev->getDamager();

				if(!$damager instanceof Human) return;

				$item = $damager->getInventory()->getItemInHand();
				$enchantments = [];

				foreach($item->getEnchantments() as $enchantmentInstance){
					$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

					if(
						$enchantment instanceof self &&
						$enchantment->isHandled() &&
						!$enchantment->isDisabled()
					) $enchantments[] = $enchantment;
				}

				$enchantments = self::sortOrder($enchantments);

				foreach($enchantments as $enchantment){
					if($ev instanceof Cancellable && $ev->isCancelled()) return;

					$enchantment->react($damager, $enchantment->getStoredLevel(), $ev);
				}

				if($ev->getFinalDamage() > 11){
					$ev->setModifier(11 - $ev->getFinalDamage(), EnchantmentUtils::MODIFIER_DAMAGE_CORRECTION);
				}
			},
			($event instanceof BlockBreakEvent || $event instanceof PlayerInteractEvent) => function(BlockBreakEvent|PlayerInteractEvent $ev) : void{
				$player = $ev->getPlayer();
				$item = $ev->getItem();
				$enchantments = [];

				foreach($item->getEnchantments() as $enchantmentInstance){
					$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

					if(
						$enchantment instanceof self &&
						$enchantment->isHandled() &&
						!$enchantment->isDisabled()
					) $enchantments[] = $enchantment;
				}

				$enchantments = self::sortOrder($enchantments);

				foreach($enchantments as $enchantment){
					if($ev instanceof Cancellable && $ev->isCancelled()) return;

					$enchantment->react($player, $enchantment->getStoredLevel(), $ev);
				}
			},
			$event instanceof KeyFindEvent => function(KeyFindEvent $ev) : void{
				$player = $ev->getPlayer();

				if(!$player instanceof Human) return;

				$item = $player->getInventory()->getItemInHand();
				$enchantments = [];

				foreach($item->getEnchantments() as $enchantmentInstance){
					$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

					if(
						$enchantment instanceof self &&
						$enchantment->isHandled() &&
						!$enchantment->isDisabled()
					) $enchantments[] = $enchantment;
				}

				$enchantments = self::sortOrder($enchantments);

				foreach($enchantments as $enchantment){
					if($ev instanceof Cancellable && $ev->isCancelled()) return;

					$enchantment->react($player, $enchantment->getStoredLevel(), $ev);
				}
			},
			$event instanceof FishingEvent => function(FishingEvent $ev) : void{
				$player = $ev->getPlayer();

				if(!$player instanceof Human) return;

				$item = $ev->getFishingRod();
				$enchantments = [];

				foreach($item->getEnchantments() as $enchantmentInstance){
					$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

					if(
						$enchantment instanceof self &&
						$enchantment->isHandled() &&
						!$enchantment->isDisabled()
					) $enchantments[] = $enchantment;
				}

				$enchantments = self::sortOrder($enchantments);

				foreach($enchantments as $enchantment){
					if($ev instanceof Cancellable && $ev->isCancelled()) return;

					$enchantment->react($player, $enchantment->getStoredLevel(), $ev);
				}
			},
			$event instanceof EntityShootBowEvent => function(EntityShootBowEvent $ev) : void{
				$entity = $ev->getEntity();

				if(!$entity instanceof Human) return;

				$item = $ev->getBow();
				$enchantments = [];

				foreach($item->getEnchantments() as $enchantmentInstance){
					$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

					if(
						$enchantment instanceof self &&
						$enchantment->isHandled() &&
						!$enchantment->isDisabled()
					) $enchantments[] = $enchantment;
				}

				$enchantments = self::sortOrder($enchantments);

				foreach($enchantments as $enchantment){
					if($ev instanceof Cancellable && $ev->isCancelled()) return;

					$enchantment->react($entity, $enchantment->getStoredLevel(), $ev);
				}
			},
			default => function(Event $ev) : void{}
		};

		$closure($event);
	}

	/**
	 * @param self[] $enchantments
	 * 
	 * @return array<int, self>
	 */
	private static function sortOrder(array $enchantments) : array{
		/** @var array<int, self> $orderList */
		$orderList = [];
		$noOrder = [];
		$endList = [];

		foreach($enchantments as $enchantment){
			if(isset($orderList[$enchantment->getOrder()]) && $enchantment->getOrder() != 0){
				array_splice($orderList, $enchantment->getOrder(), 0, [$enchantment->getOrder() => $enchantment]);
				continue;
			}elseif($enchantment->getOrder() == 0){
				array_unshift($orderList, $enchantment);
				continue;
			}elseif($enchantment->getOrder() == -1){
				$noOrder[] = $enchantment;
				continue;
			}elseif($enchantment->getOrder() == -2) {
				array_unshift($endList, $enchantment);
				continue;
			}
			$orderList[$enchantment->getOrder()] = $enchantment;
		}

		foreach($noOrder as $enchantment) $orderList[] = $enchantment;
		foreach($endList as $enchantment) $orderList[] = $enchantment;

		return $orderList;
	}

	/**
	 * Please start the order at 0
	 */
	public function getOrder() : int{ return -1; }
}
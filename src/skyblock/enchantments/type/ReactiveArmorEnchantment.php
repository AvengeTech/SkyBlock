<?php

declare(strict_types=1);

namespace skyblock\enchantments\type;

use pocketmine\entity\Living;
use pocketmine\event\Cancellable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use prison\enchantments\type\armor\DodgeEnchantment;
use skyblock\enchantments\EnchantmentRegistry;

abstract class ReactiveArmorEnchantment extends ArmorEnchantment{

	/**
	 * 
	 * @param Living $entity			Entity wearing the armor
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
				$victim = $ev->getEntity();

				if(!$victim instanceof Living) return;

				$stackable = [];

				foreach($victim->getArmorInventory()->getContents() as $item){
					/** @var self[] $enchantments */
					$enchantments = [];

					foreach($item->getEnchantments() as $enchantmentInstance){
						$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

						if(
							$enchantment instanceof self &&
							$enchantment->isHandled() &&
							!$enchantment->isDisabled()
						){
							$enchantments[] = $enchantment;

							if($enchantment->isStackable()){
								$stackable[$enchantment->getId()] = (isset($stackable[$enchantment->getId()]) ? 
									$stackable[$enchantment->getId()] + $enchantment->getStoredLevel() : $enchantment->getStoredLevel()
								);
							}
						}
					}

					$enchantments = self::sortOrder($enchantments);

					foreach($enchantments as $enchantment){
						if($ev instanceof Cancellable && $ev->isCancelled()) return;

						$level = $enchantmentInstance->getLevel();
						$level = ($enchantment->isStackable() && isset($stackable[$enchantment->getId()]) ? $stackable[$enchantment->getId()] : $level);

						$enchantment->react($victim, min($level, ($enchantment->isStackable() ? $enchantment->getMaxStackLevel() : $enchantment->getStoredLevel())), $ev);
					}
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

		$dodge = null;

		foreach($enchantments as $enchantment){
			if ($enchantment instanceof DodgeEnchantment) {
				$dodge = $enchantment;
				continue;
			}
			if(isset($orderList[$enchantment->getOrder()]) && $enchantment->getOrder() != 0){
				array_splice($orderList, $enchantment->getOrder(), 0, [$enchantment->getOrder() => $enchantment]);
				continue;
			}elseif($enchantment->getOrder() == 0){
				array_unshift($orderList, $enchantment);
				continue;
			}elseif($enchantment->getOrder() == -1){
				$noOrder[] = $enchantment;
				continue;
			}
			$orderList[$enchantment->getOrder()] = $enchantment;
		}

		if (!is_null($dodge)) {
			// If Dodge is present, it should always be the first enchantment
			array_unshift($orderList, $dodge);
		}

		foreach($noOrder as $enchantment) $orderList[] = $enchantment;

		return $orderList;
	}

	/**
	 * Please start the order at 0
	 */
	public function getOrder() : int{ return -1; }
}
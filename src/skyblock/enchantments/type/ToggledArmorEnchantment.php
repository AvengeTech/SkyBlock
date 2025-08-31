<?php

declare(strict_types=1);

namespace skyblock\enchantments\type;

use pocketmine\entity\Living;
use pocketmine\item\Item;

use skyblock\enchantments\EnchantmentRegistry;

abstract class ToggledArmorEnchantment extends ArmorEnchantment{

	abstract protected function onActivate(Living $entity, int $enchantmentLevel) : void;
	
	abstract protected function onDeactivate(Living $entity, int $enchantmentLevel) : void;

	final public static function onToggle(Living $entity, Item $oldItem, Item $newItem) : void{
		foreach($oldItem->getEnchantments() as $enchantmentInstance){
			$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

			if($enchantment instanceof self) $enchantment->onDeactivate($entity, $enchantment->getStoredLevel());
		}

		$stackable = [];

		foreach($entity->getArmorInventory()->getContents() as $armor){
			foreach($armor->getEnchantments() as $enchantmentInstance){
				$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

				if(
					$enchantment instanceof self &&
					$enchantment->isStackable() &&
					$enchantment->isHandled() &&
					!$enchantment->isDisabled()
				){
					$stackable[$enchantment->getId()] = (isset($stackable[$enchantment->getId()]) ? 
						$stackable[$enchantment->getId()] + $enchantment->getStoredLevel() : $enchantment->getStoredLevel()
					);
				}
			}
		}

		foreach($newItem->getEnchantments() as $enchantmentInstance){
			$enchantment = EnchantmentRegistry::getEWE($enchantmentInstance);

			if(
				$enchantment instanceof self &&
				$enchantment->isHandled() &&
				!$enchantment->isDisabled()
			){
				$level = $enchantment->getStoredLevel();
				$level = min(
					($enchantment->isStackable() && isset($stackable[$enchantment->getId()]) ? $stackable[$enchantment->getId()] : $level), 
					($enchantment->isStackable() ? $enchantment->getMaxStackLevel() : $enchantment->getStoredLevel())
				);

				$enchantment->onActivate($entity, min($level, ($enchantment->isStackable() ? $enchantment->getMaxStackLevel() : $enchantment->getStoredLevel())));
			}
		}
	}
}
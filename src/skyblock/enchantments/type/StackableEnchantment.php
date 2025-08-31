<?php

declare(strict_types=1);

namespace skyblock\enchantments\type;

use skyblock\enchantments\EnchantmentData as ED;

class StackableEnchantment extends Enchantment{

	public function isStackable() : bool{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_STACKABLE] ?? false; }

	public function getMaxStackLevel() : int{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_MAX_STACK] ?? $this->getMaxLevel(); }
}
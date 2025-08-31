<?php

namespace skyblock\enchantments\type\armor;

use pocketmine\event\entity\EntityDamageEvent;
use skyblock\enchantments\type\ArmorEnchantment;

class ProtectionEnchantment extends ArmorEnchantment {

	protected float $typeModifier;
	protected ?array $applicableDamageTypes = null;

	public function __construct(int $id, array $extraData) {
		parent::__construct($id, $extraData);

		$this->typeModifier = $extraData["type_modifier"];
		$this->applicableDamageTypes = $extraData["applicable_damage_types"] ?? null;
	}

	public function getTypeModifier(): float {
		return $this->typeModifier;
	}

	public function getProtectionFactor(int $level): int {
		return (int) floor((6 + $level ** 2) * $this->typeModifier / 3);
	}

	public function isApplicable(EntityDamageEvent $event): bool {
		return is_null($this->applicableDamageTypes) || in_array($event->getCause(), $this->applicableDamageTypes ?? []);
	}
}

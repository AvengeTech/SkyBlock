<?php

declare(strict_types=1);

namespace skyblock\enchantments\type;

use core\utils\{
	ItemRegistry,
	TextFormat as TF
};

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\{
	Enchantment as PMEnch,
	EnchantmentInstance
};
use skyblock\enchantments\item\EnchantmentBook;
use pocketmine\network\mcpe\protocol\serializer\BitSet;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\enchantments\utils\EnchantmentUtils;

class Enchantment extends PMEnch{

	private int $level = -1;
	
	public function __construct(
		private int $id,
		private array $extraData
	){
		if($this->isHandled() && $this->getId() > 70) $this->register();
	}

	public function register() : self{
		EnchantmentIdMap::getInstance()->register($this->getId(), $this);

		return $this;
	}

	// Mainly used for Vanilla Enchantments
	public function getEnchantment() : PMEnch{ return EnchantmentIdMap::getInstance()->fromId($this->getId()); }

	public function getEnchantmentInstance(int $level = 1) : EnchantmentInstance{ return new EnchantmentInstance($this->getEnchantment(), $level); }

	public function getId() : int{ return $this->id; }

	public function getExtraData() : array{ return $this->extraData; }

	public function getRuntimeId() : int{ return spl_object_id($this); }

	public function getName() : string{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_NAME] ?? "N/A"; }

	public function getTypeName(): string {
		$str = "";
		foreach ($this->getETypes() as $type) {
			if (strlen($str) > 0) $str .= " & ";
			$str .= ED::ITEM_FLAG_NAMES[$type];
		}
		return $str . " ";
	}

	public function isStackable() : bool{ return false; }

	public function getDescription() : string{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_DESCRIPTION] ?? "N/A"; }

	public function getMaxLevel() : int{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_MAX_LEVEL] ?? 1; }

	public function getRarity() : int{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_RARITY] ?? ED::RARITY_COMMON; }

	public function getType(): BitSet {
		$basicType = ED::ENCHANTMENTS[$this->getId()][ED::DATA_TYPE] ?? ED::SLOT_ALL;
		if (!is_array($basicType)) $basicType = [$basicType];

		$set = new BitSet(count(ED::SLOTS) * (PHP_INT_SIZE * 8), []);
		foreach ($basicType as $f) $set->set($f, true);

		return $set;
	}

	public function canOverclock() : bool{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_OVERCLOCK] ?? false; }

	public function getClass() : string{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_CLASS] ?? self::class; }
	
	public function isObtainable() : bool{ return isset(ED::ENCHANTMENTS[$this->getId()][ED::DATA_OBTAINABLE]) ? ED::ENCHANTMENTS[$this->getId()]["obtainable"] : true;}

	/**
	 * Mostly for vanilla enchantments, whether they are handled at all by the plugin
	 */
	public function isHandled() : bool{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_HANDLED] ?? true; }

	public function isDisabled() : bool{ return ED::ENCHANTMENTS[$this->getId()][ED::DATA_DISABLED] ?? false; }

	public function getETypes(): array {
		return ED::typeToEtype($this->getType());
	}

	public function hasType(int $flag): bool {
		foreach ($this->getETypes() as $f) {
			if (($f & $flag) !== 0) return true;
		}
		return false;
	}
	
	public function getStoredLevel() : int{ return $this->level; }
	
	public function setStoredLevel(int $level) : self{
		$this->level = $level;

		return $this;
	}

	public function getLore(int $level = 1) : string{
		return TF::RESET . $this->getRarityColor() . $this->getName() . " " . EnchantmentUtils::getRoman($level);
	}

	public function getRarityColor() : string{ return ED::rarityColor($this->getRarity()); }

	public function getRarityName() : string{ return ED::rarityName($this->getRarity()); }

	public function asBook() : EnchantmentBook{
		$book = ItemRegistry::REDEEMED_BOOK();
		return $book->setup($this, $book->getApplyCost(), $book->getApplyChance(), $book->getEnchantmentCategory(), $book->hasRerolled(), $book->getRerolledEnchantments());
	}
}
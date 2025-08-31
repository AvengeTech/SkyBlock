<?php

namespace skyblock\shop\data;

use pocketmine\item\{
	Item,
	ItemBlock
};
use pocketmine\utils\TextFormat;
use pocketmine\block\utils\ColoredTrait;

use core\ui\elements\simpleForm\Button;
use core\utils\conversion\LegacyItemIds;

class ShopItem {

	public function __construct(
		private int $level,
		private Item $item,
		private float $buyprice = -1,
		private float $sellprice = -1,
		private string $linkend = "",
		private string $customname = "",
		private array $extraData = []
	) {
	}

	public function getLevel(): int {
		return $this->level;
	}

	public function getItem(): Item {
		return $this->item;
	}

	public function getCustomName(): string {
		return $this->customname;
	}

	public function getName(): string {
		if (!empty(($cn = $this->getCustomName()))) return $cn;

		$item = $this->getItem();
		$name = $item->getName();
		if (method_exists($item, 'getColor')) {
			/** @var ColoredTrait $item */
			$name = $item->getColor()->getDisplayName() . " " . $name;
		}
		if ($item instanceof ItemBlock) {
			$block = $item->getBlock();
			if (method_exists($block, 'getColor')) {
				/** @var ColoredTrait $block */
				$name = $block->getColor()->getDisplayName() . " " . $name;
			}
		}
		return TextFormat::clean($name);
	}

	public function canBuy(): bool {
		return $this->buyprice != -1;
	}

	public function getBuyPrice(): float {
		return $this->buyprice;
	}

	public function canSell(): bool {
		return $this->sellprice != -1;
	}

	public function getSellPrice(): float {
		return $this->sellprice;
	}

	public function getLinkEnd(): string {
		return $this->linkend;
	}

	public function getExtraData(): array {
		return $this->extraData;
	}

	public function getImagePath(): string {
		return "[REDACTED]";
	}

	public function getImage(): string {
		return $this->getImagePath() . ($this->getLinkEnd() === "" ? LegacyItemIds::typeIdToLegacyId($this->getItem()->getTypeId()) . "-" . LegacyItemIds::stateIdToMeta($this->getItem()) . ".png" : $this->getLinkEnd());
	}

	public function getButton(): Button {
		if ($this->canBuy() && !$this->canSell()) {
			$text = "Can buy ONLY";
		} elseif (!$this->canBuy() && $this->canSell()) {
			$text = "Can sell ONLY";
		} else {
			$text = "Can be bought and sold";
		}
		$button = new Button($this->getName() . "\n" . $text);
		$button->addImage(Button::IMAGE_TYPE_URL, $this->getImage());

		return $button;
	}
}
